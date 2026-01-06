<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Responsable extends Model
{
    protected $fillable = [
        'nombre',
        'apellido',
        'tipo_documento', // Enum logic handled in service/validation usually, or simple string
        'documento',
        'cargo',
        'email',
        'telefono',
        'sede_id',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    // 'nombre_completo' is virtual in DB (generated column), but we can access it.
    // However, Laravel doesn't fill generated columns.
    
    public function sede(): BelongsTo
    {
        return $this->belongsTo(Sede::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }
    
    public function ubicacionesACargo(): HasMany
    {
        return $this->hasMany(Ubicacion::class, 'responsable_id');
    }
    
    public function sedesCoordinadas(): HasMany
    {
        return $this->hasMany(Sede::class, 'coordinador_id');
    }
}
