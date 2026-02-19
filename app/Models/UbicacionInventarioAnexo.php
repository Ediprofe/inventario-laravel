<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UbicacionInventarioAnexo extends Model
{
    protected $table = 'ubicacion_inventario_anexos';

    protected $fillable = [
        'ubicacion_id',
        'titulo',
        'tipo',
        'archivo_pdf_path',
        'fecha_corte',
        'responsable_fuente',
        'adjuntar_en_envio',
        'activo',
    ];

    protected $casts = [
        'fecha_corte' => 'date',
        'adjuntar_en_envio' => 'boolean',
        'activo' => 'boolean',
    ];

    public function ubicacion(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class);
    }
}
