<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class MigrateSqliteToPgsql extends Command
{
    protected $signature = 'inventory:migrate-sqlite-to-pgsql
        {--from=sqlite_legacy : Conexión origen}
        {--to=pgsql_migration : Conexión destino}
        {--chunk=500 : Tamaño de lote para copiado}
        {--truncate : Vacía tablas destino antes de copiar}
        {--force : Ejecuta la migración (sin este flag solo hace preflight)}
        {--skip-backup : Omite backup de seguridad}
        {--backup-dir=storage/backups/migration : Carpeta de backups locales}';

    protected $description = 'Migra datos de SQLite a PostgreSQL con preflight, backup y verificación';

    /**
     * @var array<int, string>
     */
    protected array $tableOrder = [
        'users',
        'sedes',
        'responsables',
        'ubicacions',
        'articulos',
        'items',
        'historial_movimientos',
        'envios_inventario',
        'ubicacion_inventario_anexos',
    ];

    /**
     * @var array<string, array<int, string>>
     */
    protected array $excludedColumns = [
        'responsables' => ['nombre_completo'],
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $from = (string) $this->option('from');
        $to = (string) $this->option('to');
        $chunk = max(1, (int) $this->option('chunk'));
        $force = (bool) $this->option('force');
        $truncate = (bool) $this->option('truncate');

        if ($from === $to) {
            $this->error('La conexión origen y destino no pueden ser la misma.');

            return self::FAILURE;
        }

        if (! $this->connectionExists($from) || ! $this->connectionExists($to)) {
            return self::FAILURE;
        }

        if (! $this->checkConnections($from, $to)) {
            return self::FAILURE;
        }

        if (! $this->checkTablesExist($from, $to)) {
            return self::FAILURE;
        }

        $this->warn('Preflight OK.');
        $this->line("Origen: <info>{$from}</info> | Destino: <info>{$to}</info>");

        $sourceCounts = $this->tableCounts($from);
        $targetCounts = $this->tableCounts($to);

        $this->table(['Tabla', 'Origen', 'Destino'], collect($this->tableOrder)
            ->map(fn (string $table): array => [$table, $sourceCounts[$table], $targetCounts[$table]])
            ->all());

        if (! $force) {
            $this->comment('Modo preflight: no se copiaron datos.');
            $this->line('Para ejecutar realmente:');
            $this->line("php artisan inventory:migrate-sqlite-to-pgsql --from={$from} --to={$to} --force --truncate");

            return self::SUCCESS;
        }

        if (! $truncate && collect($targetCounts)->sum() > 0) {
            $this->error('La base destino tiene datos. Use --truncate para evitar duplicados o limpie manualmente.');

            return self::FAILURE;
        }

        if (! (bool) $this->option('skip-backup')) {
            $this->backupSourceData($from, (string) $this->option('backup-dir'));
        } else {
            $this->warn('Se omitió backup (--skip-backup).');
        }

        if ($truncate) {
            $this->truncateDestination($to);
        }

        try {
            DB::connection($to)->beginTransaction();

            $this->copyTables($from, $to, $chunk);
            $this->syncSequences($to);

            DB::connection($to)->commit();
        } catch (Throwable $e) {
            DB::connection($to)->rollBack();
            $this->error('Migración fallida: '.$e->getMessage());

            return self::FAILURE;
        }

        $finalTargetCounts = $this->tableCounts($to);
        $mismatch = collect($this->tableOrder)->first(
            fn (string $table): bool => (int) $sourceCounts[$table] !== (int) $finalTargetCounts[$table]
        );

        if ($mismatch) {
            $this->error("Conteo inconsistente en {$mismatch}. Revise antes de cambiar DB_CONNECTION.");

            return self::FAILURE;
        }

        $this->info('Migración completada y validada por conteos.');
        $this->table(['Tabla', 'Origen', 'Destino'], collect($this->tableOrder)
            ->map(fn (string $table): array => [$table, $sourceCounts[$table], $finalTargetCounts[$table]])
            ->all());

        return self::SUCCESS;
    }

    protected function connectionExists(string $connection): bool
    {
        if (! config()->has("database.connections.{$connection}")) {
            $this->error("No existe la conexión [{$connection}] en config/database.php.");

            return false;
        }

        return true;
    }

    protected function checkConnections(string $from, string $to): bool
    {
        try {
            DB::connection($from)->getPdo();
        } catch (Throwable $e) {
            $this->error("No fue posible conectar al origen [{$from}]: ".$e->getMessage());

            return false;
        }

        try {
            DB::connection($to)->getPdo();
        } catch (Throwable $e) {
            $this->error("No fue posible conectar al destino [{$to}]: ".$e->getMessage());

            return false;
        }

        $targetDriver = (string) config("database.connections.{$to}.driver");
        if ($targetDriver !== 'pgsql') {
            $this->warn("Advertencia: la conexión destino [{$to}] usa driver [{$targetDriver}], no pgsql.");
        }

        return true;
    }

    protected function checkTablesExist(string $from, string $to): bool
    {
        foreach ($this->tableOrder as $table) {
            if (! Schema::connection($from)->hasTable($table)) {
                $this->error("Falta tabla [{$table}] en origen [{$from}].");

                return false;
            }

            if (! Schema::connection($to)->hasTable($table)) {
                $this->error("Falta tabla [{$table}] en destino [{$to}]. Ejecute migraciones en destino primero.");

                return false;
            }
        }

        return true;
    }

    /**
     * @return array<string, int>
     */
    protected function tableCounts(string $connection): array
    {
        return collect($this->tableOrder)
            ->mapWithKeys(function (string $table) use ($connection): array {
                return [$table => (int) DB::connection($connection)->table($table)->count()];
            })
            ->all();
    }

    protected function backupSourceData(string $from, string $backupDir): void
    {
        $absoluteBackupDir = str_starts_with($backupDir, '/')
            ? $backupDir
            : base_path($backupDir);

        File::ensureDirectoryExists($absoluteBackupDir);

        $stamp = now()->format('Ymd_His');
        $dbPath = (string) config("database.connections.{$from}.database");

        if ($dbPath !== '' && is_file($dbPath)) {
            $dbBackupPath = "{$absoluteBackupDir}/database_{$stamp}.sqlite";
            File::copy($dbPath, $dbBackupPath);
            $this->info("Backup DB creado: {$dbBackupPath}");
        } else {
            $this->warn("No se detectó archivo SQLite de origen en [{$dbPath}].");
        }

        $storageSource = storage_path('app/public');
        if (is_dir($storageSource)) {
            $storageBackupDir = "{$absoluteBackupDir}/storage_public_{$stamp}";
            File::copyDirectory($storageSource, $storageBackupDir);
            $this->info("Backup de archivos creado: {$storageBackupDir}");
        }
    }

    protected function truncateDestination(string $to): void
    {
        $tables = collect($this->tableOrder)
            ->map(fn (string $table): string => '"'.$table.'"')
            ->implode(', ');

        DB::connection($to)->statement("TRUNCATE TABLE {$tables} RESTART IDENTITY CASCADE");
        $this->warn('Tablas destino truncadas con RESTART IDENTITY CASCADE.');
    }

    protected function copyTables(string $from, string $to, int $chunk): void
    {
        $coordinadores = DB::connection($from)
            ->table('sedes')
            ->whereNotNull('coordinador_id')
            ->pluck('coordinador_id', 'id')
            ->all();

        foreach ($this->tableOrder as $table) {
            $sourceColumns = Schema::connection($from)->getColumnListing($table);
            $targetColumns = Schema::connection($to)->getColumnListing($table);
            $targetColumnTypes = $this->targetColumnTypes($to, $table);

            $columns = array_values(array_intersect($sourceColumns, $targetColumns));
            $excluded = $this->excludedColumns[$table] ?? [];
            $columns = array_values(array_diff($columns, $excluded));

            if ($table === 'sedes') {
                $columns = array_values(array_diff($columns, ['coordinador_id']));
            }

            if ($columns === []) {
                $this->warn("Sin columnas para copiar en [{$table}], se omite.");

                continue;
            }

            $copied = 0;

            DB::connection($from)
                ->table($table)
                ->orderBy('id')
                ->select($columns)
                ->chunkById($chunk, function ($rows) use ($to, $table, $targetColumnTypes, &$copied): void {
                    $payload = collect($rows)
                        ->map(fn ($row): array => $this->normalizeRow((array) $row, $targetColumnTypes))
                        ->all();

                    if ($payload !== []) {
                        DB::connection($to)->table($table)->insert($payload);
                        $copied += count($payload);
                    }
                }, 'id');

            $this->info("Copiados {$copied} registros en [{$table}].");
        }

        foreach ($coordinadores as $sedeId => $responsableId) {
            DB::connection($to)
                ->table('sedes')
                ->where('id', (int) $sedeId)
                ->update(['coordinador_id' => (int) $responsableId]);
        }
    }

    protected function syncSequences(string $to): void
    {
        $driver = (string) config("database.connections.{$to}.driver");
        if ($driver !== 'pgsql') {
            return;
        }

        foreach ($this->tableOrder as $table) {
            $maxId = DB::connection($to)->table($table)->max('id');
            $next = $maxId ? (int) $maxId : 1;
            $isCalled = $maxId ? 'true' : 'false';
            DB::connection($to)->statement(
                "SELECT setval(pg_get_serial_sequence('{$table}', 'id'), {$next}, {$isCalled})"
            );
        }
    }

    /**
     * @return array<string, string>
     */
    protected function targetColumnTypes(string $connection, string $table): array
    {
        return collect(DB::connection($connection)->select(
            "SELECT column_name, data_type FROM information_schema.columns WHERE table_schema = 'public' AND table_name = ?",
            [$table]
        ))
            ->mapWithKeys(fn ($row): array => [$row->column_name => (string) $row->data_type])
            ->all();
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<string, string>  $targetColumnTypes
     * @return array<string, mixed>
     */
    protected function normalizeRow(array $row, array $targetColumnTypes): array
    {
        foreach ($row as $column => $value) {
            $row[$column] = $this->normalizeValue($value, $targetColumnTypes[$column] ?? null);
        }

        return $row;
    }

    protected function normalizeValue(mixed $value, ?string $targetType): mixed
    {
        if ($targetType === null) {
            return $value;
        }

        if ($value === '' || $value === '?') {
            if (! Str::contains($targetType, ['character', 'text'])) {
                return null;
            }
        }

        if ($value === null) {
            return null;
        }

        if (in_array($targetType, ['smallint', 'integer', 'bigint'], true)) {
            if (is_int($value)) {
                return $value;
            }

            if (is_numeric($value)) {
                return (int) $value;
            }

            return null;
        }

        if (in_array($targetType, ['numeric', 'real', 'double precision'], true)) {
            if (is_float($value) || is_int($value)) {
                return $value;
            }

            if (is_numeric($value)) {
                return (float) $value;
            }

            return null;
        }

        if ($targetType === 'boolean') {
            if (is_bool($value)) {
                return $value;
            }

            if (is_int($value)) {
                return $value === 1;
            }

            $normalized = Str::lower(trim((string) $value));

            return match ($normalized) {
                '1', 'true', 't', 'yes', 'y', 'si', 's' => true,
                '0', 'false', 'f', 'no', 'n' => false,
                default => null,
            };
        }

        return $value;
    }
}
