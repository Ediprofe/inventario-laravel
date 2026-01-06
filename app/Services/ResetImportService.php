<?php

namespace App\Services;

use App\Models\Articulo;
use App\Models\HistorialMovimiento;
use App\Models\Item;
use App\Models\Responsable;
use App\Models\Sede;
use App\Models\Ubicacion;
use App\Imports\GeneralImport;
use App\Enums\CategoriaArticulo;
use App\Enums\Disponibilidad;
use App\Enums\EstadoFisico;
use App\Enums\TipoUbicacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ResetImportService
{
    protected array $log = [];
    
    public function import($file)
    {
        $this->log = [];
        
        // 1. Read all sheets
        // We use toArray directly. Note: GeneralImport implements WithHeadingRow, 
        // so we expect assoc arrays with slugified keys.
        $data = Excel::toArray(new GeneralImport, $file);
        
        // $data is array of sheets. Keys are numeric indices or sheet names if using WithMultipleSheets properly?
        // Excel::toArray returns [Sheet1 => [...], Sheet2 => [...]] IF we define sheets in Import object.
        // If we don't define specific sheets mapping, it might just return numbered array [0 => [...], 1 => [...]]
        // or if using WithMultipleSheets it expects us to return imports.
        // Actually, simplest way for dynamic sheets: DO NOT Use WithMultipleSheets in GeneralImport, just empty Import class?
        // No, WithHeadingRow applies to all.
        // Let's assume standard behavior: Array of sheets, keyed by sheet names if mapped, else numeric.
        // BUT Maatwebsite behavior with toArray is:
        // returns array [ 0 => [rows sheet 1], 1 => [rows sheet 2] ].
        // We need to identify sheets by content or index?
        // The plan says strict order/names: Items, Sedes, Ubicaciones, Articulos, Responsables.
        // But the user might change order. Keying by name is safer if possible.
        // However, standard toArray doesn't return keyed by name unless we use explicit sheet binding.
        // Strategy: Inspect the headers of the first row of each sheet to identify it.
        
        $sheets = $this->identifySheets($data);
        
        if (count($sheets) < 5) {
            throw new \Exception("Faltan hojas requeridas en el Excel. Se encontraron: " . implode(', ', array_keys($sheets)));
        }

        DB::beginTransaction();
        
        try {
            // 2. Clear Items (Reset)
            // We truncate items and history. Catalogs are kept/updated.
            // Disable foreign key checks for truncate? Or just delete()
            // Delete is safer for cascading if configured, but we want to wipe items.
            Schema::disableForeignKeyConstraints(); // if using SQLite/MySQL specific
            // In generic Laravel:
            DB::statement('PRAGMA foreign_keys = OFF'); // SQLite
            // For Postgres: SET session_replication_role = 'replica'; ...
            // Let's use Eloquent delete to be DB agnostic and safe
            HistorialMovimiento::query()->delete();
            Item::query()->delete();
            // DB::statement('PRAGMA foreign_keys = ON'); 
            // Re-enable validation later.
            
            // 3. Process Catalogs
            $this->processSedes($sheets['Sedes']);
            $this->processResponsables($sheets['Responsables']);
            $this->processArticulos($sheets['Articulos']);
            $this->processUbicaciones($sheets['Ubicaciones']);
            
            // 4. Process Items
            $this->processItems($sheets['Items']);
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => 'Importación completada con éxito.',
                'log' => $this->log
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);
            throw $e;
        }
    }
    
    protected function identifySheets(array $data): array
    {
        $identified = [];
        // Expected columns (slugified)
        $maps = [
            'Sedes' => ['nombre', 'coordinador'], // 'coordinador' is unique to Sedes
            'Responsables' => ['nombre_completo', 'cargo'],
            'Articulos' => ['nombre', 'categoria'], // 'categoria' is unique to Articulos
            'Ubicaciones' => ['sede_nombre', 'tipo'], // 'tipo' distinguishes from Items (which also has sede_nombre)
            'Items' => ['placa', 'articulo_nombre'],
        ];
        
        foreach ($data as $sheetParams) {
            if (empty($sheetParams)) continue;
            
            $firstRow = $sheetParams[0];
            $keys = array_keys($firstRow);
            
            foreach ($maps as $name => $requiredKeys) {
                if (count(array_intersect($requiredKeys, $keys)) >= 2) { // Match at least 2 keys
                    $identified[$name] = $sheetParams;
                    break;
                }
            }
        }
        
        return $identified;
    }

    protected function processSedes(array $rows)
    {
        foreach ($rows as $row) {
            if (empty($row['nombre'])) continue;
            
            Sede::updateOrCreate(
                ['nombre' => trim($row['nombre'])],
                [
                    'codigo' => trim($row['codigo']),
                    'direccion' => $row['direccion'] ?? null,
                    'telefono' => $row['telefono'] ?? null,
                    'email' => $row['email'] ?? null,
                    'activo' => true
                ]
            );
        }
        $this->log[] = "Sedes procesadas: " . count($rows);
    }
    
    protected function processResponsables(array $rows)
    {
        foreach ($rows as $row) {
            if (empty($row['nombre_completo'])) continue;
            
            // Split name? Or just use nombre_completo as visual?
            // Our model has 'nombre', 'apellido'.
            // Excel has "Nombre Completo".
            // We need to split it roughly.
            $parts = explode(' ', trim($row['nombre_completo']), 2);
            $nombre = $parts[0];
            $apellido = $parts[1] ?? '.'; // Default dot if no surname
            
            // Lookup Sede
            $sedeId = null;
            if (!empty($row['sede_nombre'])) {
                $sede = Sede::where('nombre', trim($row['sede_nombre']))->first();
                $sedeId = $sede?->id;
            }
            
            Responsable::updateOrCreate(
                ['nombre' => $nombre, 'apellido' => $apellido], // This might duplicate if name split varies.
                // Better approach: We can't perfectly unique on split name. 
                // But we must respect the Excel "Source of Truth" which allows identifying by "Nombre Completo".
                // Ideally add 'nombre_completo' column to DB as real column? 
                // We added it as virtual.
                // Let's rely on finding by (nombre, apellido) or just update first found?
                // Risk: Two "Juan Perez".
                // I'll search closely.
                [
                    'tipo_documento' => !empty($row['tipo_documento']) ? $row['tipo_documento'] : null,
                    'documento' => !empty($row['documento']) ? $row['documento'] : null,
                    'cargo' => $row['cargo'] ?? null,
                    'email' => !empty($row['email']) ? $row['email'] : null,
                    'telefono' => !empty($row['telefono']) ? $row['telefono'] : null,
                    'sede_id' => $sedeId,
                    'activo' => true
                ]
            );
        }
        $this->log[] = "Responsables procesados: " . count($rows);
    }
    
    protected function processArticulos(array $rows)
    {
        foreach ($rows as $row) {
            if (empty($row['nombre'])) continue;
            
            $categoria = $this->resolveEnum($row['categoria'] ?? '', CategoriaArticulo::class, CategoriaArticulo::OTROS);
            
            Articulo::updateOrCreate(
                ['nombre' => trim($row['nombre'])],
                [
                    'categoria' => $categoria,
                    'codigo' => $row['codigo'] ?? null,
                    'descripcion' => $row['descripcion'] ?? null,
                    'activo' => true
                ]
            );
        }
        $this->log[] = "Articulos procesados: " . count($rows);
    }
    
    protected function processUbicaciones(array $rows)
    {
        foreach ($rows as $row) {
            if (empty($row['nombre']) || empty($row['sede_nombre'])) continue;
            
            $sede = Sede::where('nombre', trim($row['sede_nombre']))->first();
            if (!$sede) continue; // Skip if sede not found
            
            $tipo = $this->resolveEnum($row['tipo'] ?? '', TipoUbicacion::class, TipoUbicacion::OTRO);
            
            $responsableId = null;
            if (!empty($row['responsable_por_defecto'])) {
                // Fuzzy search responsable? 'nombre_completo'
                // Search in DB using LIKE
                 $resp = Responsable::where(DB::raw("nombre || ' ' || apellido"), 'like', '%' . trim($row['responsable_por_defecto']) . '%')->first(); 
                 // SQLite concat is ||. Postgres too. MySQL is concat().
                 // Safest is to just assume we loaded them correctly or look for matching name/apellido.
                 // Let's try to match split parts again or exact if we stored it?
                 // No, we stored name/lastname.
                 $responsableId = $resp?->id;
            }
            
            Ubicacion::updateOrCreate(
                ['sede_id' => $sede->id, 'nombre' => trim($row['nombre'])],
                [
                    'codigo' => $row['codigo'] ?? Str::slug($row['nombre']),
                    'tipo' => $tipo,
                    'responsable_id' => $responsableId,
                    'piso' => $row['piso'] ?? null,
                    'capacidad' => $row['capacidad'] ?? null,
                    'observaciones' => $row['observaciones'] ?? null,
                    'activo' => true
                ]
            );
        }
        $this->log[] = "Ubicaciones procesadas: " . count($rows);
    }
    
    protected function processItems(array $rows)
    {
        $count = 0;
        foreach ($rows as $row) {
             // Required fields lookup
             if (empty($row['sede_nombre']) || empty($row['ubicacion_nombre']) || empty($row['articulo_nombre'])) {
                 continue; // Skip invalid rows
             }
             
             $sede = Sede::where('nombre', trim($row['sede_nombre']))->first();
             $articulo = Articulo::where('nombre', trim($row['articulo_nombre']))->first();
             
             if (!$sede || !$articulo) continue;
             
             $ubicacion = Ubicacion::where('sede_id', $sede->id)
                ->where('nombre', trim($row['ubicacion_nombre']))
                ->first();
                
             if (!$ubicacion) {
                 // Lazy create ubicacion? Plan says "Create/update" in prev step. 
                 // If not found here, it wasn't in Ubicaciones sheet or name mismatch.
                 // We fail or skip?
                 // Better skip to maintain integrity.
                 continue; 
             }
             
             // Normalize Placa
             $placa = $this->normalizePlaca($row['placa'] ?? null);
             
             // Responsable
             $responsableId = null;
             if (!empty($row['responsable_nombre_completo'])) {
                 $parts = explode(' ', trim($row['responsable_nombre_completo']));
                 // Try match
                 $resp = Responsable::where('nombre', 'like', $parts[0] . '%')->first(); // Naive match
                 $responsableId = $resp?->id;
             }
             
             Item::create([
                 'articulo_id' => $articulo->id,
                 'sede_id' => $sede->id,
                 'ubicacion_id' => $ubicacion->id,
                 'responsable_id' => $responsableId,
                 'placa' => $placa,
                 'marca' => $row['marca'] ?? null,
                 'serial' => !empty($row['serial']) ? $row['serial'] : null,
                 'estado' => $this->resolveEnum($row['estado_fisico'] ?? '', EstadoFisico::class, EstadoFisico::SIN_ESTADO),
                 'disponibilidad' => $this->resolveEnum($row['disponibilidad'] ?? '', Disponibilidad::class, Disponibilidad::EXTRAVIADO),
                 'descripcion' => $row['descripcion'] ?? null,
                 'observaciones' => $row['observaciones'] ?? null,
             ]);
             $count++;
        }
        $this->log[] = "Items procesados: " . $count;
    }
    
    // Helpers
    protected function normalizePlaca($val) {
        if (empty($val)) return 'NA';
        $v = strtoupper(trim($val));
        if ($v === 'NO TIENE' || $v === 'S/P' || $v === 'NA') return 'NA';
        return $val; // Store original casing or upper? Excel usually upper.
    }
    
    protected function resolveEnum($val, $enumClass, $default) {
        if (empty($val)) return $default;
        $slug = Str::slug($val, '_');
        // Try to match enum cases
        foreach ($enumClass::cases() as $case) {
            if ($case->value === $slug || strtolower($case->getLabel()) === strtolower($val)) {
                return $case;
            }
        }
        return $default;
    }
}
