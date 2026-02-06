<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Ubicacion;
use App\Models\Responsable;
use App\Enums\EstadoFisico;
use App\Enums\Disponibilidad;
use Illuminate\Support\Collection;

class InventarioReportService
{
    /**
     * Get aggregated inventory for a specific location
     * Only items with disponibilidad = 'en_uso'
     */
    public function getInventarioPorUbicacion(int $ubicacionId): array
    {
        $ubicacion = Ubicacion::with(['sede', 'responsable'])->find($ubicacionId);
        
        if (!$ubicacion) {
            return ['ubicacion' => null, 'items' => collect()];
        }
        
        $items = Item::where('ubicacion_id', $ubicacionId)
            ->where('disponibilidad', Disponibilidad::EN_USO)
            ->with('articulo')
            ->get();
        
        // Group by articulo and aggregate
        $grouped = $items->groupBy('articulo_id')->map(function ($group) {
            $articulo = $group->first()->articulo;
            
            // Count by estado
            $estadoCounts = [];
            foreach (EstadoFisico::cases() as $estado) {
                $count = $group->where('estado', $estado)->count();
                if ($count > 0) {
                    $estadoCounts[$estado->value] = [
                        'label' => $estado->getLabel(),
                        'count' => $count,
                    ];
                }
            }
            
            // Collect placas
            $placas = $group->pluck('placa')->filter(fn($p) => $p && $p !== 'NA')->values();
            
            return [
                'articulo' => $articulo->nombre,
                'cantidad' => $group->count(),
                'estados' => $estadoCounts,
                'placas' => $placas,
            ];
        })->values();
        
        return [
            'ubicacion' => $ubicacion,
            'items' => $grouped,
            'total' => $items->count(),
        ];
    }

    /**
     * Get complete inventory for a location (for PDF with detail)
     * Includes both summary and individual item details
     * Only items with disponibilidad = 'en_uso'
     */
    public function getInventarioPorUbicacionCompleto(int $ubicacionId): array
    {
        $baseData = $this->getInventarioPorUbicacion($ubicacionId);
        
        if (!$baseData['ubicacion']) {
            return array_merge($baseData, ['detalle' => collect()]);
        }
        
        // Get individual items for detail table
        $detalle = Item::where('ubicacion_id', $ubicacionId)
            ->where('disponibilidad', Disponibilidad::EN_USO)
            ->with(['articulo', 'sede', 'ubicacion', 'responsable'])
            ->orderBy('articulo_id')
            ->get();
        
        return array_merge($baseData, ['detalle' => $detalle]);
    }
    
    /**
     * Get aggregated inventory for a specific responsible person
     * Only items with disponibilidad = 'en_uso'
     */
    public function getInventarioPorResponsable(int $responsableId): array
    {
        $responsable = Responsable::find($responsableId);
        
        if (!$responsable) {
            return ['responsable' => null, 'items' => collect()];
        }
        
        $items = Item::where('responsable_id', $responsableId)
            ->where('disponibilidad', Disponibilidad::EN_USO)
            ->with(['articulo', 'ubicacion'])
            ->get();
        
        // Group by articulo + ubicacion
        $grouped = $items->groupBy(function ($item) {
            return $item->articulo_id . '_' . $item->ubicacion_id;
        })->map(function ($group) {
            $first = $group->first();
            
            // Count by estado
            $estadoCounts = [];
            foreach (EstadoFisico::cases() as $estado) {
                $count = $group->where('estado', $estado)->count();
                if ($count > 0) {
                    $estadoCounts[$estado->value] = [
                        'label' => $estado->getLabel(),
                        'count' => $count,
                    ];
                }
            }
            
            return [
                'articulo' => $first->articulo->nombre,
                'ubicacion_nombre' => $first->ubicacion->nombre,
                'ubicacion_codigo' => $first->ubicacion->codigo,
                'cantidad' => $group->count(),
                'estados' => $estadoCounts,
            ];
        })->values();
        
        return [
            'responsable' => $responsable,
            'items' => $grouped,
            'total' => $items->count(),
        ];
    }
    
    /**
     * Get complete inventory for a responsible person (for PDF with detail)
     * Includes both summary and individual item details
     * Only items with disponibilidad = 'en_uso'
     */
    public function getInventarioPorResponsableCompleto(int $responsableId): array
    {
        $baseData = $this->getInventarioPorResponsable($responsableId);
        
        if (!$baseData['responsable']) {
            return array_merge($baseData, ['detalle' => collect()]);
        }
        
        // Get individual items for detail table
        $detalle = Item::where('responsable_id', $responsableId)
            ->where('disponibilidad', Disponibilidad::EN_USO)
            ->with(['articulo', 'sede', 'ubicacion', 'responsable'])
            ->orderBy('ubicacion_id')
            ->orderBy('articulo_id')
            ->get();
        
        return array_merge($baseData, ['detalle' => $detalle]);
    }

    /**
     * Format estado breakdown as string (e.g., "Bueno: 10, Regular: 2")
     */
    public function formatEstadoBreakdown(array $estados): string
    {
        return collect($estados)
            ->map(fn($e) => $e['label'] . ': ' . $e['count'])
            ->implode(', ');
    }
}
