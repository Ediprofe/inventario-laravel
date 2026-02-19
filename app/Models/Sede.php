<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sede extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'codigo',
        'direccion',
        'telefono',
        'email',
        'coordinador_id',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function coordinador(): BelongsTo
    {
        return $this->belongsTo(Responsable::class, 'coordinador_id');
    }

    public function responsables(): HasMany
    {
        return $this->hasMany(Responsable::class);
    }

    public function ubicaciones(): HasMany
    {
        return $this->hasMany(Ubicacion::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }
}
