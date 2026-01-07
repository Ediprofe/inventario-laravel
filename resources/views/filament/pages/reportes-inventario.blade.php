<x-filament-panels::page>
    {{-- Tabs Navigation --}}
    <div class="flex space-x-1 bg-gray-100 p-1 rounded-lg w-max mb-6 dark:bg-gray-800">
        <button wire:click="$set('activeTab', 'ubicacion')"
                class="px-4 py-2 rounded-md text-sm font-medium transition-colors
                       {{ $activeTab === 'ubicacion' ? 'bg-white text-primary-600 shadow-sm dark:bg-gray-700 dark:text-primary-400' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400' }}">
            📍 Por Ubicación
        </button>
        <button wire:click="$set('activeTab', 'responsable')"
                class="px-4 py-2 rounded-md text-sm font-medium transition-colors
                       {{ $activeTab === 'responsable' ? 'bg-white text-primary-600 shadow-sm dark:bg-gray-700 dark:text-primary-400' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400' }}">
            👤 Por Responsable
        </button>
        <button wire:click="$set('activeTab', 'consolidado')"
                class="px-4 py-2 rounded-md text-sm font-medium transition-colors
                       {{ $activeTab === 'consolidado' ? 'bg-white text-primary-600 shadow-sm dark:bg-gray-700 dark:text-primary-400' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400' }}">
            📦 Consolidado Global
        </button>
    </div>

    {{-- TAB 1: POR UBICACIÓN --}}
    @if($activeTab === 'ubicacion')
        <div class="space-y-6">
            {{-- Filters --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-white p-4 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="space-y-1">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Sede</label>
                    <select wire:model.live="sedeId" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 dark:bg-gray-800 dark:border-gray-600">
                        @foreach($this->sedes as $sede)
                            <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Ubicación</label>
                    <select wire:model.live="ubicacionId" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 dark:bg-gray-800 dark:border-gray-600">
                        @foreach($this->ubicacionesForSede as $ubi)
                            <option value="{{ $ubi->id }}">{{ $ubi->codigo }} - {{ $ubi->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if($this->currentUbicacion)
                {{-- Header Card --}}
                <div class="bg-blue-50 border border-blue-100 p-6 rounded-xl flex flex-col md:flex-row items-center justify-between dark:bg-blue-900/20 dark:border-blue-800">
                    <div class="flex items-center space-x-4">
                        <div class="p-3 bg-blue-500 text-white rounded-lg">
                            <x-heroicon-o-map-pin class="w-8 h-8" />
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $this->currentUbicacion->nombre }}</h2>
                            <div class="flex flex-wrap gap-2 mt-1">
                                <span class="px-2 py-1 bg-white text-xs font-medium text-gray-600 rounded-md border border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700">
                                    # {{ $this->currentUbicacion->codigo }}
                                </span>
                                <span class="px-2 py-1 bg-white text-xs font-medium text-green-600 rounded-md border border-green-200 dark:bg-gray-800 dark:text-green-400 dark:border-green-800">
                                    🏢 {{ $this->currentUbicacion->sede->nombre }}
                                </span>
                                @if($this->currentUbicacion->responsable)
                                    <span class="px-2 py-1 bg-white text-xs font-medium text-purple-600 rounded-md border border-purple-200 dark:bg-gray-800 dark:text-purple-400 dark:border-purple-800">
                                        👤 {{ $this->currentUbicacion->responsable->nombre_completo }}
                                    </span>
                                @else
                                    <span class="px-2 py-1 bg-white text-xs font-medium text-red-600 rounded-md border border-red-200">
                                        👤 Sin Responsable
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Inventory Table --}}
                <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-950/5 overflow-hidden dark:bg-gray-900 dark:ring-white/10">
                    <div class="p-6 border-b border-gray-100 flex justify-between items-center dark:border-gray-800">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Resumen de Inventario</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Items en esta ubicación agrupados por artículo</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('reportes.pdf.ubicacion', $this->ubicacionId) }}" 
                               target="_blank"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                PDF
                            </a>
                            <span class="px-3 py-1 bg-blue-100 text-blue-700 font-bold rounded-full text-sm dark:bg-blue-900 dark:text-blue-300">
                                {{ $this->totalItemsUbicacion }} Items
                            </span>
                        </div>
                    </div>
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                            <tr>
                                <th class="px-6 py-3 font-medium">Artículo</th>
                                <th class="px-6 py-3 font-medium text-right">Cantidad</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($this->itemsPorUbicacion as $row)
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-gray-100">{{ $row['articulo'] }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-full font-bold dark:bg-blue-900/50 dark:text-blue-400">
                                            {{ $row['cantidad'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                            @if(count($this->itemsPorUbicacion) === 0)
                                <tr>
                                    <td colspan="2" class="px-6 py-12 text-center text-gray-500">No hay items en esta ubicación.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center p-12 text-gray-500">Seleccione una ubicación para ver los detalles.</div>
            @endif

            @if($this->currentUbicacion)
            <div class="mt-8">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Detalle de Items</h3>
                    <span class="px-3 py-1 bg-blue-50 text-blue-700 text-xs rounded-full dark:bg-blue-900/30 dark:text-blue-300">
                        Solo ítems "En Uso"
                    </span>
                </div>
                {{ $this->table }}
            </div>
            @endif
        </div>
    @endif

    {{-- TAB 2: POR RESPONSABLE --}}
    @if($activeTab === 'responsable')
        <div class="space-y-6">
            {{-- Filter --}}
            <div class="bg-white p-4 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                 <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Seleccionar Responsable</label>
                 <select wire:model.live="responsableFilterId" class="mt-1 w-full md:w-1/2 border-gray-300 rounded-lg shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200 dark:bg-gray-800 dark:border-gray-600">
                    <option value="">Seleccione...</option>
                    @foreach($this->responsables as $resp)
                        <option value="{{ $resp->id }}">{{ $resp->nombre_completo }}</option>
                    @endforeach
                 </select>
            </div>

            @if($this->currentResponsable)
                {{-- Header Card --}}
                <div class="bg-purple-50 border border-purple-100 p-6 rounded-xl flex items-center space-x-4 dark:bg-purple-900/20 dark:border-purple-800">
                    <div class="p-3 bg-purple-500 text-white rounded-lg">
                        <x-heroicon-o-user class="w-8 h-8" />
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $this->currentResponsable->nombre_completo }}</h2>
                        <div class="flex gap-2 mt-1">
                             <span class="px-2 py-1 bg-white text-xs font-medium text-gray-600 rounded-md border border-gray-200 dark:bg-gray-800 dark:text-gray-300">
                                {{ $this->currentResponsable->cargo ?? 'Sin Cargo' }}
                             </span>
                        </div>
                    </div>
                    <div class="ml-auto flex items-center gap-3">
                        <a href="{{ route('reportes.excel.responsable', $this->responsableFilterId) }}" 
                           target="_blank"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Excel
                        </a>
                         <span class="px-4 py-2 bg-purple-100 text-purple-700 font-bold rounded-full dark:bg-purple-900 dark:text-purple-300">
                            {{ $this->totalItemsResponsable }} Items Total
                        </span>
                    </div>
                </div>

                {{-- Table --}}
                 <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-950/5 overflow-hidden dark:bg-gray-900 dark:ring-white/10">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                            <tr>
                                <th class="px-6 py-3 font-medium">Cód. Ubicación</th>
                                <th class="px-6 py-3 font-medium">Ubicación</th>
                                <th class="px-6 py-3 font-medium">Artículo</th>
                                <th class="px-6 py-3 font-medium text-right">Cantidad</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($this->itemsPorResponsable as $row)
                                <tr class="group hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400 font-mono text-xs">{{ $row['codigo_ubicacion'] }}</td>
                                    <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                                        <div class="flex items-center gap-2">
                                            <x-heroicon-o-map-pin class="w-4 h-4 text-gray-400" />
                                            {{ $row['ubicacion'] }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-gray-100">{{ $row['articulo'] }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="px-3 py-1 bg-purple-50 text-purple-600 rounded-full font-bold dark:bg-purple-900/50 dark:text-purple-400">
                                            {{ $row['cantidad'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                            @if(count($this->itemsPorResponsable) === 0)
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-gray-500">Este responsable no tiene items asignados.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="mt-8">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Detalle de Items Asignados</h3>
                        <span class="px-3 py-1 bg-blue-50 text-blue-700 text-xs rounded-full dark:bg-blue-900/30 dark:text-blue-300">
                            Solo ítems "En Uso"
                        </span>
                    </div>
                     {{ $this->table }}
                </div>
            @endif
        </div>
    @endif

    {{-- TAB 3: CONSOLIDADO (Matrix) --}}
    @if($activeTab === 'consolidado')
        <div class="space-y-6">
            {{-- Filters --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-white p-4 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                 
                 {{-- Info Note: Only En Uso items --}}
                 <div class="space-y-1">
                     <label class="text-sm font-medium text-gray-700 dark:text-gray-200 flex items-center gap-2">
                        <x-heroicon-o-information-circle class="w-4 h-4 text-blue-500" /> Nota
                     </label>
                     <div class="px-3 py-2 bg-blue-50 text-blue-700 text-sm rounded-lg dark:bg-blue-900/30 dark:text-blue-300">
                         Solo se muestran ítems con disponibilidad <strong>"En Uso"</strong>.
                     </div>
                 </div>

                 {{-- Article Filter --}}
                 <div class="space-y-1">
                     <label class="text-sm font-medium text-gray-700 dark:text-gray-200 flex items-center gap-2">
                        <x-heroicon-o-cube class="w-4 h-4" /> Artículo (Opcional)
                     </label>
                     <select wire:model.live="articuloFilterId" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 dark:bg-gray-800 dark:border-gray-600">
                        <option value="">Todos los artículos</option>
                        @foreach($this->articulosOptions as $id => $nombre)
                            <option value="{{ $id }}">{{ $nombre }}</option>
                        @endforeach
                     </select>
                 </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-950/5 overflow-x-auto dark:bg-gray-900 dark:ring-white/10">
                 <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-gray-50 border-b border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                        <tr>
                            <th class="px-6 py-4 font-bold text-gray-900 sticky left-0 bg-gray-50 dark:bg-gray-800 dark:text-white z-10">Artículo</th>
                            @foreach($this->matrixData['sedes'] as $sede)
                                <th class="px-6 py-4 font-semibold text-center text-gray-700 dark:text-gray-300 border-l border-gray-200 dark:border-gray-700">
                                    {{ $sede->nombre }}
                                </th>
                            @endforeach
                            <th class="px-6 py-4 font-bold text-gray-900 text-center border-l-2 border-gray-200 dark:border-gray-700 dark:text-white">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($this->matrixData['rows'] as $row)
                            <tr class="group hover:bg-gray-50 dark:hover:bg-white/5 transition-colors duration-150">
                                <td class="px-6 py-4 font-medium text-gray-900 sticky left-0 bg-white dark:bg-gray-900 group-hover:bg-gray-50 dark:group-hover:bg-gray-800 dark:text-gray-100 z-10 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)] transition-colors">
                                    {{ $row['nombre'] }}
                                </td>
                                @foreach($this->matrixData['sedes'] as $sede)
                                    <td class="px-4 py-3 text-center border-l border-gray-100 dark:border-gray-800 align-top">
                                        @php $cell = $row['sedes'][$sede->id]; @endphp
                                        @if($cell['total'] > 0)
                                            <div class="flex flex-col items-center justify-center gap-1.5 min-w-[80px]">
                                                {{-- Total --}}
                                                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Total: {{ $cell['total'] }}</span>
                                                
                                                {{-- Breakdown List --}}
                                                <div class="w-full space-y-1">
                                                    @foreach($cell['breakdown'] as $b)
                                                        <div class="flex items-center justify-between px-2 py-1 rounded text-xs
                                                            {{ match($b['color']) {
                                                                'success' => 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                                                'warning' => 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                                                                'danger'  => 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                                                default   => 'bg-gray-50 text-gray-700 dark:bg-gray-800 dark:text-gray-400'
                                                            } }}">
                                                            <span class="font-medium">{{ $b['label'] }}</span>
                                                            <span class="font-bold border-l border-current pl-1.5 ml-1.5 opacity-75">{{ $b['qty'] }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-gray-300 dark:text-gray-700 text-xs">-</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="px-6 py-4 font-black text-center text-blue-600 bg-blue-50/50 border-l-2 border-gray-100 dark:bg-blue-900/10 dark:text-blue-400 dark:border-gray-700 text-lg">
                                    {{ $row['total_row'] }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                 </table>
            </div>
        </div>
    @endif
</x-filament-panels::page>
