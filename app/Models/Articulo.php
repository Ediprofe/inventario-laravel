<?php

namespace App\Models;

use App\Enums\CategoriaArticulo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Articulo extends Model
{
    protected $fillable = [
        'nombre',
        'categoria',
        'codigo',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'categoria' => CategoriaArticulo::class,
        'activo' => 'boolean',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }
}
