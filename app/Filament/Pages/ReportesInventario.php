<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Sede;
use App\Models\Ubicacion;
use App\Models\Responsable;
use App\Models\Item;
use App\Models\Articulo;
use App\Enums\EstadoFisico;
use Illuminate\Support\Collection;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class ReportesInventario extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(function (Builder $query) {
                // If inactive tab or no filter, return empty
                if ($this->activeTab === 'ubicacion' && $this->ubicacionId) {
                    return Item::query()->where('ubicacion_id', $this->ubicacionId);
                }
                if ($this->activeTab === 'responsable' && $this->responsableFilterId) {
                    return Item::query()->where('responsable_id', $this->responsableFilterId);
                }
                return Item::query()->whereRaw('1 = 0');
            })
            ->columns([
                Tables\Columns\TextColumn::make('placa')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('articulo.nombre')
                    ->label('Artículo')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('marca')
                    ->searchable(),
                Tables\Columns\TextColumn::make('serial')
                    ->searchable(),
                Tables\Columns\TextColumn::make('estado')
                    ->badge(),
                Tables\Columns\TextColumn::make('responsable.nombre_completo')
                    ->label('Responsable')
                    ->hidden(fn () => $this->activeTab === 'responsable'), // Hide if redundant
                Tables\Columns\TextColumn::make('ubicacion.nombre')
                    ->label('Ubicación')
                    ->description(fn (Item $item) => $item->ubicacion->codigo)
                    ->hidden(fn () => $this->activeTab === 'ubicacion'), // Hide if redundant
            ])
            ->summary(null); // No summary needed in footer
    }
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Reportes de Inventario';
    protected static ?string $title = 'Reportes y Analítica';
    protected static ?string $slug = 'reportes-inventario';
    
    protected static string $view = 'filament.pages.reportes-inventario';

    // Tabs
    public $activeTab = 'ubicacion'; // ubicacion, responsable, consolidado

    // Filters - Ubicacion
    public $sedeId;
    public $ubicacionId;
    
    // Filters - Responsable
    public $responsableFilterId;

    // Filters - Consolidado
    public $disponibilidadFilter = 'en_uso';
    public $articuloFilterId = '';

    public function mount()
    {
        // Defaults
        $firstSede = Sede::first();
        if ($firstSede) {
            $this->sedeId = $firstSede->id;
            $firstUbi = Ubicacion::where('sede_id', $this->sedeId)->first();
            $this->ubicacionId = $firstUbi?->id;
        }
    }

    // --- Computed Data Helpers ---

    public function getSedesProperty()
    {
        return Sede::all();
    }

    public function getUbicacionesForSedeProperty()
    {
        if (!$this->sedeId) return [];
        return Ubicacion::where('sede_id', $this->sedeId)->get();
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
        
        return Item::where('ubicacion_id', $this->ubicacionId)
            ->selectRaw('articulo_id, count(*) as total')
            ->with('articulo')
            ->groupBy('articulo_id')
            ->get()
            ->map(function ($row) {
                return [
                    'articulo' => $row->articulo->nombre ?? 'Desconocido',
                    'cantidad' => $row->total,
                ];
            });
    }

    public function getTotalItemsUbicacionProperty()
    {
        return $this->itemsPorUbicacion->sum('cantidad');
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
        
        // Detailed list: Articulo | Ubicacion | Cantidad
        return Item::where('responsable_id', $this->responsableFilterId)
            ->selectRaw('articulo_id, ubicacion_id, count(*) as total')
            ->with(['articulo', 'ubicacion'])
            ->groupBy('articulo_id', 'ubicacion_id')
            ->get()
            ->map(function ($row) {
                return [
                    'articulo' => $row->articulo->nombre ?? '?',
                    'ubicacion' => $row->ubicacion->nombre ?? '?',
                    'codigo_ubicacion' => $row->ubicacion->codigo ?? '',
                    'cantidad' => $row->total,
                ];
            });
    }

    public function getTotalItemsResponsableProperty()
    {
        return $this->itemsPorResponsable->sum('cantidad');
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
        
        // Optimization: Fetch aggregated data in one query
        $query = Item::query();
        
        if ($this->disponibilidadFilter) {
            $query->where('disponibilidad', $this->disponibilidadFilter);
        }
        
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
    
    protected function getColorForEstado(EstadoFisico $estado): string
    {
        return match ($estado) {
            EstadoFisico::BUENO => 'success',
            EstadoFisico::REGULAR => 'warning',
            EstadoFisico::MALO => 'danger',
            default => 'gray',
        };
    }
}
