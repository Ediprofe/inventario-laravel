<?php

namespace App\Services;

use App\Enums\Disponibilidad;
use App\Enums\EstadoFisico;
use App\Models\Item;
use App\Models\Responsable;
use App\Models\Ubicacion;

class InventarioReportService
{
    /**
     * Get aggregated inventory for a specific location
     * Includes all disponibilidades and estados
     */
    public function getInventarioPorUbicacion(int $ubicacionId): array
    {
        $ubicacion = Ubicacion::with(['sede', 'responsable'])->find($ubicacionId);

        if (! $ubicacion) {
            return ['ubicacion' => null, 'items' => collect()];
        }

        $items = Item::where('ubicacion_id', $ubicacionId)
            ->with('articulo')
            ->get();

        // Group by articulo and aggregate (all records)
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

            // Count by disponibilidad
            $disponibilidadCounts = [];
            foreach (Disponibilidad::cases() as $disponibilidad) {
                $count = $group->where('disponibilidad', $disponibilidad)->count();
                if ($count > 0) {
                    $disponibilidadCounts[$disponibilidad->value] = [
                        'label' => $disponibilidad->getLabel(),
                        'count' => $count,
                    ];
                }
            }

            // Collect placas
            $placas = $group->pluck('placa')->filter(fn ($p) => $p && $p !== 'NA')->values();

            return [
                'articulo' => $articulo->nombre,
                'cantidad' => $group->count(),
                'estados' => $estadoCounts,
                'disponibilidades' => $disponibilidadCounts,
                'placas' => $placas,
            ];
        })->values();

        $resumenDisponibilidad = [];
        foreach (Disponibilidad::cases() as $disponibilidad) {
            $count = $items->where('disponibilidad', $disponibilidad)->count();
            $resumenDisponibilidad[$disponibilidad->value] = [
                'label' => $disponibilidad->getLabel(),
                'count' => $count,
            ];
        }

        $resumenEstado = [];
        foreach (EstadoFisico::cases() as $estado) {
            $count = $items->where('estado', $estado)->count();
            $resumenEstado[$estado->value] = [
                'label' => $estado->getLabel(),
                'count' => $count,
            ];
        }

        return [
            'ubicacion' => $ubicacion,
            'items' => $grouped,
            'total' => $items->count(),
            'total_en_uso' => $resumenDisponibilidad[Disponibilidad::EN_USO->value]['count'] ?? 0,
            'resumen_disponibilidad' => $resumenDisponibilidad,
            'resumen_estado' => $resumenEstado,
        ];
    }

    /**
     * Get complete inventory for a location (for PDF with detail)
     * Includes both summary and individual item details
     */
    public function getInventarioPorUbicacionCompleto(int $ubicacionId): array
    {
        $baseData = $this->getInventarioPorUbicacion($ubicacionId);

        if (! $baseData['ubicacion']) {
            return array_merge($baseData, ['detalle' => collect()]);
        }

        // Get individual items for detail table (all + en_uso subset)
        $detalle = Item::where('ubicacion_id', $ubicacionId)
            ->with(['articulo', 'sede', 'ubicacion', 'responsable'])
            ->orderBy('articulo_id')
            ->get();

        $detalleEnUso = $detalle->where('disponibilidad', Disponibilidad::EN_USO)->values();

        return array_merge($baseData, [
            'detalle' => $detalle,
            'detalle_en_uso' => $detalleEnUso,
        ]);
    }

    /**
     * Get aggregated inventory for a specific responsible person
     * Includes all disponibilidades and estados
     */
    public function getInventarioPorResponsable(int $responsableId): array
    {
        $responsable = Responsable::find($responsableId);

        if (! $responsable) {
            return ['responsable' => null, 'items' => collect()];
        }

        $items = Item::where('responsable_id', $responsableId)
            ->with(['articulo', 'ubicacion'])
            ->get();

        // Group by articulo + ubicacion
        $grouped = $items->groupBy(function ($item) {
            return $item->articulo_id.'_'.$item->ubicacion_id;
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

            $disponibilidadCounts = [];
            foreach (Disponibilidad::cases() as $disponibilidad) {
                $count = $group->where('disponibilidad', $disponibilidad)->count();
                if ($count > 0) {
                    $disponibilidadCounts[$disponibilidad->value] = [
                        'label' => $disponibilidad->getLabel(),
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
                'disponibilidades' => $disponibilidadCounts,
            ];
        })->values();

        $resumenDisponibilidad = [];
        foreach (Disponibilidad::cases() as $disponibilidad) {
            $count = $items->where('disponibilidad', $disponibilidad)->count();
            $resumenDisponibilidad[$disponibilidad->value] = [
                'label' => $disponibilidad->getLabel(),
                'count' => $count,
            ];
        }

        $resumenEstado = [];
        foreach (EstadoFisico::cases() as $estado) {
            $count = $items->where('estado', $estado)->count();
            $resumenEstado[$estado->value] = [
                'label' => $estado->getLabel(),
                'count' => $count,
            ];
        }

        return [
            'responsable' => $responsable,
            'items' => $grouped,
            'total' => $items->count(),
            'total_en_uso' => $resumenDisponibilidad[Disponibilidad::EN_USO->value]['count'] ?? 0,
            'resumen_disponibilidad' => $resumenDisponibilidad,
            'resumen_estado' => $resumenEstado,
        ];
    }

    /**
     * Get complete inventory for a responsible person (for PDF with detail)
     * Includes both summary and individual item details
     */
    public function getInventarioPorResponsableCompleto(int $responsableId): array
    {
        $baseData = $this->getInventarioPorResponsable($responsableId);

        if (! $baseData['responsable']) {
            return array_merge($baseData, ['detalle' => collect()]);
        }

        // Get individual items for detail table (all + en_uso subset)
        $detalle = Item::where('responsable_id', $responsableId)
            ->with(['articulo', 'sede', 'ubicacion', 'responsable'])
            ->orderBy('ubicacion_id')
            ->orderBy('articulo_id')
            ->get();

        $detalleEnUso = $detalle->where('disponibilidad', Disponibilidad::EN_USO)->values();

        return array_merge($baseData, [
            'detalle' => $detalle,
            'detalle_en_uso' => $detalleEnUso,
        ]);
    }

    /**
     * Format estado breakdown as string (e.g., "Bueno: 10, Regular: 2")
     */
    public function formatEstadoBreakdown(array $estados): string
    {
        return collect($estados)
            ->map(fn ($e) => $e['label'].': '.$e['count'])
            ->implode(', ');
    }
}
