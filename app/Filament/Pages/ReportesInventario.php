<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Sede;
use App\Models\Ubicacion;
use App\Models\Responsable;
use App\Models\Item;
use App\Models\Articulo;
use App\Enums\EstadoFisico;
use App\Filament\Resources\ItemResource;
use Illuminate\Support\Collection;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Url;

class ReportesInventario extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        // Reuse the exact columns, actions (Edit), and bulk actions from the main ItemResource
        $table = ItemResource::table($table);

        // Apply our specific context filters and fix EditAction form
        return $table
            ->query(function (Builder $query) {
                // All queries are filtered by disponibilidad = 'en_uso' using scope
                if ($this->activeTab === 'ubicacion' && $this->ubicacionId) {
                    $query = Item::enUso()->where('ubicacion_id', $this->ubicacionId);

                    if ($this->detalleArticuloId) {
                        $query->where('articulo_id', $this->detalleArticuloId);
                    }

                    if ($this->detalleEstado) {
                        $query->where('estado', $this->detalleEstado);
                    }

                    return $query;
                }
                if ($this->activeTab === 'responsable' && $this->responsableFilterId) {
                    $query = Item::enUso()->where('responsable_id', $this->responsableFilterId);

                    if ($this->detalleResponsableArticuloId) {
                        $query->where('articulo_id', $this->detalleResponsableArticuloId);
                    }

                    if ($this->detalleResponsableUbicacionId) {
                        $query->where('ubicacion_id', $this->detalleResponsableUbicacionId);
                    }

                    if ($this->detalleResponsableEstado) {
                        $query->where('estado', $this->detalleResponsableEstado);
                    }

                    return $query;
                }

                if ($this->activeTab === 'consolidado') {
                    $query = Item::enUso();

                    if ($this->detalleConsolidadoArticuloId) {
                        $query->where('articulo_id', $this->detalleConsolidadoArticuloId);
                    } elseif ($this->articuloFilterId) {
                        $query->where('articulo_id', $this->articuloFilterId);
                    }

                    if ($this->detalleConsolidadoSedeId) {
                        $query->where('sede_id', $this->detalleConsolidadoSedeId);
                    }

                    if ($this->detalleConsolidadoEstado) {
                        $query->where('estado', $this->detalleConsolidadoEstado);
                    }

                    return $query;
                }

                return Item::query()->whereRaw('1 = 0');
            })
            ->actions([
                Tables\Actions\Action::make('edit')
                    ->label('Editar')
                    ->icon('heroicon-o-pencil')
                    ->url(fn ($record) => '/admin/items/' . $record->getKey() . '/edit?return_to=' . urlencode($this->getContextUrl())),
            ])
            ->columns(
                collect($table->getColumns())
                    ->map(function ($column) {
                        if (!method_exists($column, 'getName')) {
                            return $column;
                        }

                        if (in_array($column->getName(), ['marca', 'serial', 'descripcion', 'observaciones', 'updated_at'], true)) {
                            $column->toggleable(isToggledHiddenByDefault: false);
                        }

                        if (in_array($column->getName(), ['descripcion', 'observaciones'], true)) {
                            $column->limit(120)->wrap();
                        }

                        return $column;
                    })
                    ->toArray()
            );
    }
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Inventario por Ubicación';
    protected static ?string $title = 'Inventario por Ubicación';
    protected static ?string $slug = 'reportes-inventario';
    protected static ?int $navigationSort = 20;

    protected static string $view = 'filament.pages.reportes-inventario';

    protected string $defaultTab = 'ubicacion';
    public bool $showTabNavigation = false;

    // Current mode
    public string $activeTab = 'ubicacion'; // ubicacion, responsable, consolidado

    // Filters - Ubicacion
    #[Url(as: 'sede', history: true)]
    public ?int $sedeId = null;
    #[Url(as: 'ubicacion', history: true)]
    public ?int $ubicacionId = null;
    
    // Filters - Responsable
    #[Url(as: 'responsable', history: true)]
    public ?int $responsableFilterId = null;

    // Filters - Consolidado
    #[Url(as: 'articulo', history: true)]
    public ?int $articuloFilterId = null;

    // Quick filters - Detalle por ubicación
    public ?int $detalleArticuloId = null;
    public ?string $detalleEstado = null;

    // Quick filters - Detalle por responsable
    public ?int $detalleResponsableArticuloId = null;
    public ?int $detalleResponsableUbicacionId = null;
    public ?string $detalleResponsableEstado = null;

    // Quick filters - Detalle consolidado
    public ?int $detalleConsolidadoArticuloId = null;
    public ?int $detalleConsolidadoSedeId = null;
    public ?string $detalleConsolidadoEstado = null;

    // Email Modal Properties
    public bool $showEmailModal = false;
    public string $emailModalType = ''; // 'ubicacion' or 'responsable'
    public ?string $emailDestinatario = null;
    public ?string $emailAddress = null;
    public ?int $emailTargetId = null;
    public bool $emailSending = false;

    public function mount()
    {
        $this->activeTab = $this->defaultTab;

        if ($this->activeTab === 'ubicacion') {
            $this->restoreUbicacionFilters();
        }

        if ($this->activeTab === 'responsable') {
            $this->restoreResponsableFilter();
        }

        if ($this->activeTab === 'consolidado') {
            $this->restoreConsolidadoFilter();
        }
    }

    protected function restoreUbicacionFilters(): void
    {
        if (!$this->sedeId) {
            $this->sedeId = Session::get('reportes.ubicacion.sede_id');
        }

        if (!$this->ubicacionId) {
            $this->ubicacionId = Session::get('reportes.ubicacion.ubicacion_id');
        }

        if (!$this->sedeId) {
            $this->sedeId = Sede::query()->value('id');
        }

        if ($this->sedeId && (!$this->ubicacionId || !Ubicacion::where('id', $this->ubicacionId)->where('sede_id', $this->sedeId)->exists())) {
            $this->ubicacionId = Ubicacion::where('sede_id', $this->sedeId)->value('id');
        }

        $this->persistUbicacionFilters();
    }

    protected function restoreResponsableFilter(): void
    {
        if (!$this->responsableFilterId) {
            $this->responsableFilterId = Session::get('reportes.responsable.id');
        }
    }

    protected function restoreConsolidadoFilter(): void
    {
        if (!$this->articuloFilterId) {
            $this->articuloFilterId = Session::get('reportes.consolidado.articulo_id');
        }
    }

    protected function persistUbicacionFilters(): void
    {
        Session::put('reportes.ubicacion.sede_id', $this->sedeId);
        Session::put('reportes.ubicacion.ubicacion_id', $this->ubicacionId);
    }

    public function updatedSedeId($value): void
    {
        $this->sedeId = $value ? (int) $value : null;

        if ($this->sedeId && (!$this->ubicacionId || !Ubicacion::where('id', $this->ubicacionId)->where('sede_id', $this->sedeId)->exists())) {
            $this->ubicacionId = Ubicacion::where('sede_id', $this->sedeId)->value('id');
        }

        $this->limpiarFiltroDetalleUbicacion();
        $this->persistUbicacionFilters();
    }

    public function updatedUbicacionId($value): void
    {
        $this->ubicacionId = $value ? (int) $value : null;
        $this->limpiarFiltroDetalleUbicacion();
        $this->persistUbicacionFilters();
    }

    public function updatedResponsableFilterId($value): void
    {
        $this->responsableFilterId = $value ? (int) $value : null;
        $this->limpiarFiltroDetalleResponsable();
        Session::put('reportes.responsable.id', $this->responsableFilterId);
    }

    public function updatedArticuloFilterId($value): void
    {
        $this->articuloFilterId = $value ? (int) $value : null;
        $this->limpiarFiltroDetalleConsolidado();
        Session::put('reportes.consolidado.articulo_id', $this->articuloFilterId);
    }

    // --- Computed Data Helpers ---

    public function getSedesProperty()
    {
        return Sede::all();
    }

    public function getUbicacionesForSedeProperty()
    {
        if (!$this->sedeId) {
            return collect();
        }

        return Ubicacion::where('sede_id', $this->sedeId)->get();
    }

    public function getCanGoPreviousUbicacionProperty(): bool
    {
        if (!$this->ubicacionId) {
            return false;
        }

        $ubicaciones = $this->ubicacionesForSede->pluck('id')->values();
        $index = $ubicaciones->search($this->ubicacionId);

        return $index !== false && $index > 0;
    }

    public function getCanGoNextUbicacionProperty(): bool
    {
        if (!$this->ubicacionId) {
            return false;
        }

        $ubicaciones = $this->ubicacionesForSede->pluck('id')->values();
        $index = $ubicaciones->search($this->ubicacionId);

        return $index !== false && $index < ($ubicaciones->count() - 1);
    }

    public function goToPreviousUbicacion(): void
    {
        $ubicaciones = $this->ubicacionesForSede->pluck('id')->values();
        $index = $ubicaciones->search($this->ubicacionId);

        if ($index === false || $index <= 0) {
            return;
        }

        $this->ubicacionId = $ubicaciones->get($index - 1);
        $this->persistUbicacionFilters();
    }

    public function goToNextUbicacion(): void
    {
        $ubicaciones = $this->ubicacionesForSede->pluck('id')->values();
        $index = $ubicaciones->search($this->ubicacionId);

        if ($index === false || $index >= ($ubicaciones->count() - 1)) {
            return;
        }

        $this->ubicacionId = $ubicaciones->get($index + 1);
        $this->persistUbicacionFilters();
    }

    public function getResponsablesProperty()
    {
        return Responsable::orderBy('nombre')->get();
    }
    
    public function getArticulosOptionsProperty()
    {
        return Articulo::orderBy('nombre')->pluck('nombre', 'id');
    }

    // --- Tab 1: Ubicación Data ---

    public function getCurrentUbicacionProperty()
    {
        if (!$this->ubicacionId) return null;
        return Ubicacion::with('responsable', 'sede')->find($this->ubicacionId);
    }

    public function getItemsPorUbicacionProperty()
    {
        if (!$this->ubicacionId) return [];
        
        // Step 1: Get raw data with estado breakdown
        $rawData = Item::enUso()
            ->where('ubicacion_id', $this->ubicacionId)
            ->selectRaw('articulo_id, estado, count(*) as total')
            ->with('articulo')
            ->groupBy('articulo_id', 'estado')
            ->get();
        
        // Step 2: Group by articulo and build breakdown
        $grouped = $rawData->groupBy('articulo_id');
        
        return $grouped->map(function ($items, $articuloId) {
            $firstItem = $items->first();
            $totalQty = $items->sum('total');
            
            $breakdown = [];
            foreach (EstadoFisico::cases() as $estado) {
                $qty = $items->where('estado', $estado)->sum('total');
                if ($qty > 0) {
                    $breakdown[] = [
                        'value' => $estado->value,
                        'label' => $estado->getLabel(),
                        'color' => $this->getColorForEstado($estado),
                        'qty' => $qty,
                    ];
                }
            }
            
            return [
                'articulo_id' => $firstItem->articulo_id,
                'articulo' => $firstItem->articulo->nombre ?? 'Desconocido',
                'cantidad' => $totalQty,
                'breakdown' => $breakdown,
            ];
        })->values();
    }

    public function getTotalItemsUbicacionProperty()
    {
        return $this->itemsPorUbicacion->sum('cantidad');
    }

    public function filtrarDetalleUbicacion(int $articuloId, string $estado): void
    {
        $this->detalleArticuloId = $articuloId;
        $this->detalleEstado = $estado;
        $this->resetTable();
    }

    public function limpiarFiltroDetalleUbicacion(): void
    {
        $this->detalleArticuloId = null;
        $this->detalleEstado = null;
        $this->resetTable();
    }

    public function getDetalleArticuloSeleccionadoProperty(): ?Articulo
    {
        if (!$this->detalleArticuloId) {
            return null;
        }

        return Articulo::find($this->detalleArticuloId);
    }

    public function getDetalleEstadoSeleccionadoLabelProperty(): ?string
    {
        if (!$this->detalleEstado) {
            return null;
        }

        return EstadoFisico::tryFrom($this->detalleEstado)?->getLabel();
    }

    // --- Tab 2: Responsable Data ---

    public function getCurrentResponsableProperty()
    {
        if (!$this->responsableFilterId) return null;
        return Responsable::find($this->responsableFilterId);
    }

    public function getItemsPorResponsableProperty()
    {
        if (!$this->responsableFilterId) return [];
        
        // Resumen: Cód. Ubicación | Ubicación | Artículo | Cantidad + desglose por estado
        $rawData = Item::enUso()
            ->where('responsable_id', $this->responsableFilterId)
            ->selectRaw('articulo_id, ubicacion_id, estado, count(*) as total')
            ->with(['articulo', 'ubicacion'])
            ->groupBy('articulo_id', 'ubicacion_id', 'estado')
            ->orderBy('ubicacion_id')
            ->get();

        return $rawData
            ->groupBy(fn ($row) => $row->articulo_id . '_' . $row->ubicacion_id)
            ->map(function ($items) {
                $first = $items->first();

                $breakdown = [];
                foreach (EstadoFisico::cases() as $estado) {
                    $qty = $items->where('estado', $estado)->sum('total');
                    if ($qty > 0) {
                        $breakdown[] = [
                            'value' => $estado->value,
                            'label' => $estado->getLabel(),
                            'color' => $this->getColorForEstado($estado),
                            'qty' => $qty,
                        ];
                    }
                }

                return [
                    'articulo_id' => $first->articulo_id,
                    'ubicacion_id' => $first->ubicacion_id,
                    'codigo_ubicacion' => $first->ubicacion->codigo ?? '',
                    'ubicacion' => $first->ubicacion->nombre ?? '?',
                    'articulo' => $first->articulo->nombre ?? '?',
                    'cantidad' => $items->sum('total'),
                    'breakdown' => $breakdown,
                ];
            })
            ->values();
    }

    public function getTotalItemsResponsableProperty()
    {
        return $this->itemsPorResponsable->sum('cantidad');
    }

    public function filtrarDetalleResponsable(int $articuloId, int $ubicacionId, string $estado): void
    {
        $this->detalleResponsableArticuloId = $articuloId;
        $this->detalleResponsableUbicacionId = $ubicacionId;
        $this->detalleResponsableEstado = $estado;
        $this->resetTable();
    }

    public function limpiarFiltroDetalleResponsable(): void
    {
        $this->detalleResponsableArticuloId = null;
        $this->detalleResponsableUbicacionId = null;
        $this->detalleResponsableEstado = null;
        $this->resetTable();
    }

    public function getDetalleResponsableArticuloSeleccionadoProperty(): ?Articulo
    {
        return $this->detalleResponsableArticuloId ? Articulo::find($this->detalleResponsableArticuloId) : null;
    }

    public function getDetalleResponsableUbicacionSeleccionadaProperty(): ?Ubicacion
    {
        return $this->detalleResponsableUbicacionId ? Ubicacion::find($this->detalleResponsableUbicacionId) : null;
    }

    public function getDetalleResponsableEstadoSeleccionadoLabelProperty(): ?string
    {
        return $this->detalleResponsableEstado ? EstadoFisico::tryFrom($this->detalleResponsableEstado)?->getLabel() : null;
    }

    // --- Tab 3: Matrix (Consolidado) ---
    
    public function getMatrixDataProperty()
    {
        // Rows: Articulos
        // Cols: Sedes
        // Content: Breakdown by Estado
        
        $sedes = Sede::all();
        
        $articulosQuery = Articulo::orderBy('nombre');
        if ($this->articuloFilterId) {
            $articulosQuery->where('id', $this->articuloFilterId);
        }
        $articulos = $articulosQuery->get();
        
        // Optimization: Fetch aggregated data in one query, always filtered by en_uso
        $query = Item::enUso();
        
        // If sorting by article, we can also optimize the item query to only fetch relevant items
        if ($this->articuloFilterId) {
            $query->where('articulo_id', $this->articuloFilterId);
        }
        
        $rawData = $query->selectRaw('articulo_id, sede_id, estado, count(*) as total')
            ->groupBy('articulo_id', 'sede_id', 'estado')
            ->get();
            
        // Build the matrix structure
        $matrix = [];
        
        foreach ($articulos as $art) {
            $row = [
                'id' => $art->id,
                'nombre' => $art->nombre,
                'total_row' => 0,
                'sedes' => []
            ];
            
            foreach ($sedes as $sede) {
                // Find data for this cell
                $cellData = $rawData->where('articulo_id', $art->id)->where('sede_id', $sede->id);
                $cellTotal = $cellData->sum('total');
                
                $breakdown = [];
                foreach (EstadoFisico::cases() as $estado) {
                    $qty = $cellData->where('estado', $estado)->sum('total');
                    if ($qty > 0) {
                        $breakdown[$estado->value] = [
                            'value' => $estado->value,
                            'label' => $estado->getLabel(),
                            'color' => $this->getColorForEstado($estado),
                            'qty' => $qty
                        ];
                    }
                }
                
                $row['sedes'][$sede->id] = [
                    'total' => $cellTotal,
                    'breakdown' => $breakdown
                ];
                $row['total_row'] += $cellTotal;
            }
            
            // Only add rows that have items? Or display all? Display all is safer for inventory check.
            // Let's filter out empty rows to keep it clean if user wants summary.
            if ($row['total_row'] > 0) {
                 $matrix[] = $row;
            }
        }
        
        return [
            'sedes' => $sedes,
            'rows' => $matrix
        ];
    }

    public function filtrarDetalleConsolidado(int $articuloId, int $sedeId, string $estado): void
    {
        $this->detalleConsolidadoArticuloId = $articuloId;
        $this->detalleConsolidadoSedeId = $sedeId;
        $this->detalleConsolidadoEstado = $estado;
        $this->resetTable();
    }

    public function limpiarFiltroDetalleConsolidado(): void
    {
        $this->detalleConsolidadoArticuloId = null;
        $this->detalleConsolidadoSedeId = null;
        $this->detalleConsolidadoEstado = null;
        $this->resetTable();
    }

    public function getDetalleConsolidadoArticuloSeleccionadoProperty(): ?Articulo
    {
        return $this->detalleConsolidadoArticuloId ? Articulo::find($this->detalleConsolidadoArticuloId) : null;
    }

    public function getDetalleConsolidadoSedeSeleccionadaProperty(): ?Sede
    {
        return $this->detalleConsolidadoSedeId ? Sede::find($this->detalleConsolidadoSedeId) : null;
    }

    public function getDetalleConsolidadoEstadoSeleccionadoLabelProperty(): ?string
    {
        return $this->detalleConsolidadoEstado ? EstadoFisico::tryFrom($this->detalleConsolidadoEstado)?->getLabel() : null;
    }

    public function getCreateItemUrlProperty(): string
    {
        $query = [
            'sede_id' => $this->sedeId,
            'ubicacion_id' => $this->ubicacionId,
            'responsable_id' => $this->currentUbicacion?->responsable_id,
            'return_to' => $this->getContextUrl(),
        ];

        return '/admin/items/create?' . http_build_query(array_filter($query, fn ($value) => $value !== null && $value !== ''));
    }

    public function getBatchItemsUrlProperty(): string
    {
        $query = [
            'sede_id' => $this->sedeId,
            'ubicacion_id' => $this->ubicacionId,
            'responsable_id' => $this->currentUbicacion?->responsable_id,
            'open_batch' => 1,
            'return_to' => $this->getContextUrl(),
        ];

        return '/admin/items?' . http_build_query(array_filter($query, fn ($value) => $value !== null && $value !== ''));
    }

    protected function getContextUrl(): string
    {
        $basePath = request()->path();

        $query = match ($this->activeTab) {
            'ubicacion' => [
                'sede' => $this->sedeId,
                'ubicacion' => $this->ubicacionId,
            ],
            'responsable' => [
                'responsable' => $this->responsableFilterId,
            ],
            'consolidado' => [
                'articulo' => $this->articuloFilterId,
            ],
            default => [],
        };

        $query = array_filter($query, fn ($value) => $value !== null && $value !== '');

        return '/' . $basePath . (empty($query) ? '' : '?' . http_build_query($query));
    }
    
    protected function getColorForEstado(EstadoFisico $estado): string
    {
        return match ($estado) {
            EstadoFisico::BUENO => 'success',
            EstadoFisico::REGULAR => 'warning',
            EstadoFisico::MALO => 'danger',
            default => 'gray',
        };
    }

    // --- Email Modal Methods ---

    public function openEmailModalUbicacion(): void
    {
        $ubicacion = $this->currentUbicacion;
        
        if (!$ubicacion) {
            Notification::make()
                ->title('Error')
                ->body('Seleccione una ubicación primero')
                ->danger()
                ->send();
            return;
        }

        if (!$ubicacion->responsable) {
            Notification::make()
                ->title('Sin responsable')
                ->body('Esta ubicación no tiene un responsable asignado')
                ->warning()
                ->send();
            return;
        }

        if (!$ubicacion->responsable->email) {
            Notification::make()
                ->title('Email no registrado')
                ->body("El responsable {$ubicacion->responsable->nombre_completo} no tiene email registrado")
                ->warning()
                ->send();
            return;
        }

        $this->emailModalType = 'ubicacion';
        $this->emailDestinatario = $ubicacion->responsable->nombre_completo;
        $this->emailAddress = $ubicacion->responsable->email;
        $this->emailTargetId = $ubicacion->id;
        $this->showEmailModal = true;
    }

    public function openEmailModalResponsable(): void
    {
        $responsable = $this->currentResponsable;
        
        if (!$responsable) {
            Notification::make()
                ->title('Error')
                ->body('Seleccione un responsable primero')
                ->danger()
                ->send();
            return;
        }

        if (!$responsable->email) {
            Notification::make()
                ->title('Email no registrado')
                ->body("El responsable {$responsable->nombre_completo} no tiene email registrado")
                ->warning()
                ->send();
            return;
        }

        $this->emailModalType = 'responsable';
        $this->emailDestinatario = $responsable->nombre_completo;
        $this->emailAddress = $responsable->email;
        $this->emailTargetId = $responsable->id;
        $this->showEmailModal = true;
    }

    public function closeEmailModal(): void
    {
        $this->showEmailModal = false;
        $this->emailModalType = '';
        $this->emailDestinatario = null;
        $this->emailAddress = null;
        $this->emailTargetId = null;
    }

    public function sendEmail(): void
    {
        $this->emailSending = true;

        try {
            $controller = app(\App\Http\Controllers\ReportesPdfController::class);
            $response = $this->emailModalType === 'ubicacion'
                ? $controller->enviarUbicacion((int) $this->emailTargetId)
                : $controller->enviarResponsable((int) $this->emailTargetId);

            $data = $response->getData(true);

            if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300 && ($data['success'] ?? false)) {
                Notification::make()
                    ->title('Correo enviado')
                    ->body($data['message'] ?? 'El reporte fue enviado exitosamente')
                    ->success()
                    ->send();
                $this->closeEmailModal();
            } else {
                Notification::make()
                    ->title('Error al enviar')
                    ->body($data['message'] ?? 'No se pudo enviar el correo')
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('Error de conexión: ' . $e->getMessage())
                ->danger()
                ->send();
        } finally {
            $this->emailSending = false;
        }
    }
}
