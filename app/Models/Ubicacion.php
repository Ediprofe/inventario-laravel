<?php

namespace App\Models;

use App\Enums\TipoUbicacion;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ubicacion extends Model
{
    use HasFactory;

    protected $fillable = [
        'sede_id',
        'nombre',
        'codigo',
        'tipo',
        'responsable_id',
        'piso',
        'capacidad',
        'observaciones',
        'activo',
    ];

    protected $casts = [
        'tipo' => TipoUbicacion::class,
        'activo' => 'boolean',
        'piso' => 'integer',
        'capacidad' => 'integer',
    ];

    public function sede(): BelongsTo
    {
        return $this->belongsTo(Sede::class);
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Responsable::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function anexosInventarioInterno(): HasMany
    {
        return $this->hasMany(UbicacionInventarioAnexo::class);
    }
}
