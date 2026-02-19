<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudAjusteInventario extends Model
{
    use HasFactory;

    protected $table = 'solicitudes_ajuste_inventario';

    protected $appends = [
        'codigo_solicitud',
    ];

    protected $fillable = [
        'envio_inventario_id',
        'responsable_id',
        'ubicacion_id',
        'tipo_solicitud',
        'estado',
        'solicitante_nombre',
        'medio_contacto',
        'contacto_detalle',
        'franja_horaria',
        'detalle',
        'confirmado_coordinacion',
        'solicitado_at',
        'revisado_por_user_id',
        'revisado_at',
        'observacion_admin',
    ];

    protected $casts = [
        'confirmado_coordinacion' => 'boolean',
        'solicitado_at' => 'datetime',
        'revisado_at' => 'datetime',
    ];

    public function envioInventario(): BelongsTo
    {
        return $this->belongsTo(EnvioInventario::class);
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Responsable::class);
    }

    public function ubicacion(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class);
    }

    public function revisadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revisado_por_user_id');
    }

    public function getCodigoSolicitudAttribute(): string
    {
        return 'CIT-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    public static function resetConsecutive(): void
    {
        $model = new static;
        $connection = $model->getConnection();
        $driver = $connection->getDriverName();
        $table = $model->getTable();
        $key = $model->getKeyName();
        $maxId = (int) static::query()->max($key);
        $nextValue = $maxId > 0 ? $maxId + 1 : 1;

        if ($driver === 'pgsql') {
            $connection->selectOne('select setval(pg_get_serial_sequence(?, ?), ?, false)', [
                $table,
                $key,
                $nextValue,
            ]);

            return;
        }

        if ($driver === 'sqlite') {
            $sequenceTableExists = (int) (($connection->selectOne(
                "select count(*) as count from sqlite_master where type = 'table' and name = 'sqlite_sequence'"
            )->count ?? 0));

            if ($sequenceTableExists === 0) {
                return;
            }

            if ($maxId === 0) {
                $connection->delete('delete from sqlite_sequence where name = ?', [$table]);

                return;
            }

            $updatedRows = $connection->update('update sqlite_sequence set seq = ? where name = ?', [$maxId, $table]);
            if ($updatedRows === 0) {
                $connection->insert('insert into sqlite_sequence(name, seq) values(?, ?)', [$table, $maxId]);
            }
        }
    }
}
