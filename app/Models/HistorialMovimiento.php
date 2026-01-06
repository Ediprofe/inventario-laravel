<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialMovimiento extends Model
{
    protected $fillable = [
        'item_id',
        'responsable_id', // User ID
        'tipo_movimiento',
        'detalles',
    ];

    protected $casts = [
        'detalles' => 'array',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }
}
