<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class EnvioInventario extends Model
{
    protected $table = 'envios_inventario';

    protected $appends = [
        'codigo_envio',
    ];

    protected $fillable = [
        'responsable_id',
        'tipo',
        'ubicacion_id',
        'email_enviado_a',
        'enviado_at',
        'token',
        'aprobado_at',
        'ip_aprobacion',
        'firmante_nombre',
        'firma_base64',
        'observaciones',
    ];

    protected $casts = [
        'enviado_at' => 'datetime',
        'aprobado_at' => 'datetime',
    ];

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Responsable::class);
    }

    public function ubicacion(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class);
    }

    public function estaAprobado(): bool
    {
        return $this->aprobado_at !== null;
    }

    public function estaPendiente(): bool
    {
        return $this->aprobado_at === null;
    }

    public static function generarToken(): string
    {
        return Str::uuid()->toString();
    }

    public function getCodigoEnvioAttribute(): string
    {
        return 'ENV-' . str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }
}
