<?php

namespace App\Models;

use App\Enums\Disponibilidad;
use App\Enums\EstadoFisico;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    protected $fillable = [
        'articulo_id',
        'sede_id',
        'ubicacion_id',
        'responsable_id',
        'placa',
        'marca',
        'serial',
        'estado',
        'disponibilidad',
        'descripcion',
        'observaciones',
    ];

    protected $casts = [
        'estado' => EstadoFisico::class,
        'disponibilidad' => Disponibilidad::class,
    ];

    public function articulo(): BelongsTo
    {
        return $this->belongsTo(Articulo::class);
    }

    public function sede(): BelongsTo
    {
        return $this->belongsTo(Sede::class);
    }

    public function ubicacion(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class);
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Responsable::class);
    }
    
    public function historialMovimientos(): HasMany
    {
        return $this->hasMany(HistorialMovimiento::class);
    }
}
