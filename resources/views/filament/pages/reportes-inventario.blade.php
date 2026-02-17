<x-filament-panels::page>
    <div id="reportes-inventario-root">
        <div class="flex flex-wrap gap-2 mb-6">
            <a href="/admin/reportes-inventario"
               class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $activeTab === 'ubicacion' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300' }}">
                📍 Por Ubicación
            </a>
            <a href="/admin/reportes-inventario-responsables"
               class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $activeTab === 'responsable' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300' }}">
                👤 Por Responsable
            </a>
            <a href="/admin/reportes-inventario-consolidado"
               class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $activeTab === 'consolidado' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300' }}">
                📦 Consolidado Global
            </a>
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
                <div class="space-y-1">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Navegación rápida</label>
                    <div class="flex gap-2">
                        <button wire:click="goToPreviousUbicacion"
                                @disabled(!$this->canGoPreviousUbicacion)
                                class="inline-flex items-center gap-1 px-3 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 disabled:opacity-40 disabled:cursor-not-allowed dark:bg-gray-800 dark:text-gray-300">
                            ← Anterior
                        </button>
                        <button wire:click="goToNextUbicacion"
                                @disabled(!$this->canGoNextUbicacion)
                                class="inline-flex items-center gap-1 px-3 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 disabled:opacity-40 disabled:cursor-not-allowed dark:bg-gray-800 dark:text-gray-300">
                            Siguiente →
                        </button>
                    </div>
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

                <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-950/5 p-5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 mb-3">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Observaciones de la ubicación</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Edición rápida para el barrido en sitio.</p>
                        </div>
                        <a href="/admin/ubicacions/{{ $this->currentUbicacion->id }}/edit"
                           target="_blank"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300">
                            Abrir edición completa
                        </a>
                    </div>
                    <textarea
                        wire:model.defer="ubicacionObservaciones"
                        rows="3"
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 dark:bg-gray-800 dark:border-gray-600"
                        placeholder="Escriba aquí observaciones de esta ubicación..."
                    ></textarea>
                    <div class="mt-3 flex justify-end">
                        <button
                            wire:click="saveUbicacionObservaciones"
                            type="button"
                            class="inline-flex items-center gap-2 px-3 py-1.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700"
                        >
                            <span wire:loading.remove wire:target="saveUbicacionObservaciones">Guardar observaciones</span>
                            <span wire:loading wire:target="saveUbicacionObservaciones">Guardando...</span>
                        </button>
                    </div>
                </div>

                {{-- Inventory Table --}}
                <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-950/5 overflow-hidden dark:bg-gray-900 dark:ring-white/10">
                    <div class="p-6 border-b border-gray-100 flex justify-between items-center dark:border-gray-800">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Resumen de Inventario</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Items en esta ubicación agrupados por artículo. Haz clic en Estado o Disponibilidad para filtrar el detalle.</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="{{ $this->createItemUrl }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                                + Nuevo ítem aquí
                            </a>
                            <a href="{{ $this->batchItemsUrl }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-600 text-white text-sm font-medium rounded-lg hover:bg-amber-700 transition-colors">
                                + Agregar lote aquí
                            </a>
                            <a href="{{ route('reportes.pdf.ubicacion', $this->ubicacionId, false) }}" 
                               target="_blank"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                PDF
                            </a>
                            <a href="{{ route('reportes.excel.ubicacion', $this->ubicacionId, false) }}" 
                               target="_blank"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Excel
                            </a>
                            <button onclick="generarEnlaceFirma('{{ route('reportes.pdf.ubicacion.enviar', $this->ubicacionId, false) }}', this)"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Generar link firma
                            </button>
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
                                <th class="px-6 py-3 font-medium">Disponibilidad</th>
                                <th class="px-6 py-3 font-medium">Estado</th>
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
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($row['disponibilidad_breakdown'] as $b)
                                                <button
                                                    wire:click="filtrarDisponibilidadUbicacion({{ $row['articulo_id'] }}, '{{ $b['value'] }}')"
                                                    type="button"
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium transition hover:opacity-80
                                                    {{ match($b['color']) {
                                                        'success' => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400',
                                                        'warning' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-400',
                                                        'danger'  => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400',
                                                        'gray'    => 'bg-slate-200 text-slate-900 dark:bg-slate-600 dark:text-slate-100',
                                                        default   => 'bg-slate-200 text-slate-900 dark:bg-slate-600 dark:text-slate-100'
                                                    } }}">
                                                    {{ $b['label'] }}: {{ $b['qty'] }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($row['estado_breakdown'] as $b)
                                                <button
                                                    wire:click="filtrarDetalleUbicacion({{ $row['articulo_id'] }}, '{{ $b['value'] }}')"
                                                    type="button"
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium transition hover:opacity-80
                                                    {{ match($b['color']) {
                                                        'success' => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400',
                                                        'warning' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-400',
                                                        'danger'  => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400',
                                                        'gray'    => 'bg-slate-200 text-slate-900 dark:bg-slate-600 dark:text-slate-100',
                                                        default   => 'bg-slate-200 text-slate-900 dark:bg-slate-600 dark:text-slate-100'
                                                    } }}">
                                                    {{ $b['label'] }}: {{ $b['qty'] }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            @if(count($this->itemsPorUbicacion) === 0)
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-gray-500">No hay items en esta ubicación.</td>
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
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Detalle de Items</h3>
                        @if($this->detalleArticuloSeleccionado && ($this->detalleEstadoSeleccionadoLabel || $this->detalleDisponibilidadSeleccionadaLabel))
                            <p class="text-xs text-gray-600 dark:text-gray-300 mt-1">
                                Filtro activo: <strong>{{ $this->detalleArticuloSeleccionado->nombre }}</strong>
                                @if($this->detalleEstadoSeleccionadoLabel)
                                    + Estado <strong>{{ $this->detalleEstadoSeleccionadoLabel }}</strong>
                                @endif
                                @if($this->detalleDisponibilidadSeleccionadaLabel)
                                    + Disponibilidad <strong>{{ $this->detalleDisponibilidadSeleccionadaLabel }}</strong>
                                @endif
                            </p>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        @if($this->detalleArticuloSeleccionado || $this->detalleEstadoSeleccionadoLabel || ($this->detalleDisponibilidad && $this->detalleDisponibilidad !== 'en_uso'))
                            <button
                                wire:click="limpiarFiltroDetalleUbicacion"
                                type="button"
                                class="px-3 py-1 bg-amber-100 text-amber-800 text-xs rounded-full hover:bg-amber-200 dark:bg-amber-900/30 dark:text-amber-300"
                            >
                                Limpiar filtro rápido
                            </button>
                        @endif
                        <span class="px-3 py-1 bg-blue-50 text-blue-700 text-xs rounded-full dark:bg-blue-900/30 dark:text-blue-300">
                            Disponibilidad: {{ $this->detalleDisponibilidadSeleccionadaLabel ?? 'Todas' }}
                        </span>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    {{ $this->table }}
                </div>
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
                        <a href="{{ $this->createItemUrlResponsable }}"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                            + Nuevo ítem
                        </a>
                        <a href="{{ $this->batchItemsUrlResponsable }}"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-600 text-white text-sm font-medium rounded-lg hover:bg-amber-700 transition-colors">
                            + Agregar lote
                        </a>
                        <a href="{{ route('reportes.pdf.responsable', $this->responsableFilterId, false) }}" 
                           target="_blank"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            PDF
                        </a>
                        <a href="{{ route('reportes.excel.responsable', $this->responsableFilterId, false) }}" 
                           target="_blank"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Excel
                        </a>
                        <button onclick="generarEnlaceFirma('{{ route('reportes.pdf.responsable.enviar', $this->responsableFilterId, false) }}', this)"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            Generar link firma
                        </button>
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
                                <th class="px-6 py-3 font-medium">Disponibilidad</th>
                                <th class="px-6 py-3 font-medium">Estado</th>
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
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($row['disponibilidad_breakdown'] as $b)
                                                <button
                                                    wire:click="filtrarDisponibilidadResponsable({{ $row['articulo_id'] }}, {{ $row['ubicacion_id'] }}, '{{ $b['value'] }}')"
                                                    type="button"
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium transition hover:opacity-80
                                                    {{ match($b['color']) {
                                                        'success' => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400',
                                                        'warning' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-400',
                                                        'danger'  => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400',
                                                        'gray'    => 'bg-slate-200 text-slate-900 dark:bg-slate-600 dark:text-slate-100',
                                                        default   => 'bg-slate-200 text-slate-900 dark:bg-slate-600 dark:text-slate-100'
                                                    } }}">
                                                    {{ $b['label'] }}: {{ $b['qty'] }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($row['estado_breakdown'] as $b)
                                                <button
                                                    wire:click="filtrarDetalleResponsable({{ $row['articulo_id'] }}, {{ $row['ubicacion_id'] }}, '{{ $b['value'] }}')"
                                                    type="button"
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium transition hover:opacity-80
                                                    {{ match($b['color']) {
                                                        'success' => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400',
                                                        'warning' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-400',
                                                        'danger'  => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400',
                                                        'gray'    => 'bg-slate-200 text-slate-900 dark:bg-slate-600 dark:text-slate-100',
                                                        default   => 'bg-slate-200 text-slate-900 dark:bg-slate-600 dark:text-slate-100'
                                                    } }}">
                                                    {{ $b['label'] }}: {{ $b['qty'] }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            @if(count($this->itemsPorResponsable) === 0)
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">Este responsable no tiene items asignados.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="mt-8">
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Detalle de Items Asignados</h3>
                            @if($this->detalleResponsableArticuloSeleccionado && ($this->detalleResponsableEstadoSeleccionadoLabel || $this->detalleResponsableDisponibilidadSeleccionadaLabel))
                                <p class="text-xs text-gray-600 dark:text-gray-300 mt-1">
                                    Filtro activo: <strong>{{ $this->detalleResponsableArticuloSeleccionado->nombre }}</strong>
                                    @if($this->detalleResponsableUbicacionSeleccionada)
                                        en <strong>{{ $this->detalleResponsableUbicacionSeleccionada->codigo }}</strong>
                                    @endif
                                    @if($this->detalleResponsableEstadoSeleccionadoLabel)
                                        + Estado <strong>{{ $this->detalleResponsableEstadoSeleccionadoLabel }}</strong>
                                    @endif
                                    @if($this->detalleResponsableDisponibilidadSeleccionadaLabel)
                                        + Disponibilidad <strong>{{ $this->detalleResponsableDisponibilidadSeleccionadaLabel }}</strong>
                                    @endif
                                </p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            @if($this->detalleResponsableArticuloSeleccionado || $this->detalleResponsableEstadoSeleccionadoLabel || ($this->detalleResponsableDisponibilidad && $this->detalleResponsableDisponibilidad !== 'en_uso'))
                                <button
                                    wire:click="limpiarFiltroDetalleResponsable"
                                    type="button"
                                    class="px-3 py-1 bg-amber-100 text-amber-800 text-xs rounded-full hover:bg-amber-200 dark:bg-amber-900/30 dark:text-amber-300"
                                >
                                    Limpiar filtro rápido
                                </button>
                            @endif
                            <span class="px-3 py-1 bg-blue-50 text-blue-700 text-xs rounded-full dark:bg-blue-900/30 dark:text-blue-300">
                                Disponibilidad: {{ $this->detalleResponsableDisponibilidadSeleccionadaLabel ?? 'Todas' }}
                            </span>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        {{ $this->table }}
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- TAB 3: CONSOLIDADO (Matrix) --}}
    @if($activeTab === 'consolidado')
        <div class="space-y-6">
            {{-- Filters --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-white p-4 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                 <div class="space-y-1">
                     <label class="text-sm font-medium text-gray-700 dark:text-gray-200 flex items-center gap-2">
                        <x-heroicon-o-information-circle class="w-4 h-4 text-blue-500" /> Nota
                     </label>
                     <div class="px-3 py-2 bg-blue-50 text-blue-700 text-sm rounded-lg dark:bg-blue-900/30 dark:text-blue-300">
                         Haz clic en chips de <strong>Estado</strong> o <strong>Disponibilidad</strong> para abrir el detalle filtrado.
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
                                                
                                                <div class="w-full space-y-1">
                                                    @foreach($cell['disponibilidad_breakdown'] as $b)
                                                        <button
                                                            wire:click="filtrarDisponibilidadConsolidado({{ $row['id'] }}, {{ $sede->id }}, '{{ $b['value'] }}')"
                                                            type="button"
                                                            class="w-full flex items-center justify-between px-2 py-1 rounded text-xs transition hover:opacity-80
                                                            {{ match($b['color']) {
                                                                'success' => 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                                                'warning' => 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                                                                'danger'  => 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                                                'gray'    => 'bg-slate-200 text-slate-900 dark:bg-slate-600 dark:text-slate-100',
                                                                default   => 'bg-slate-200 text-slate-900 dark:bg-slate-600 dark:text-slate-100'
                                                            } }}">
                                                            <span class="font-medium">{{ $b['label'] }}</span>
                                                            <span class="font-bold border-l border-current pl-1.5 ml-1.5 opacity-75">{{ $b['qty'] }}</span>
                                                        </button>
                                                    @endforeach
                                                </div>

                                                <div class="w-full border-t border-gray-100 pt-1 dark:border-gray-800">
                                                    <div class="w-full space-y-1">
                                                        @foreach($cell['breakdown'] as $b)
                                                            <button
                                                                wire:click="filtrarDetalleConsolidado({{ $row['id'] }}, {{ $sede->id }}, '{{ $b['value'] }}')"
                                                                type="button"
                                                                class="w-full flex items-center justify-between px-2 py-1 rounded text-xs transition hover:opacity-80
                                                                {{ match($b['color']) {
                                                                    'success' => 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                                                    'warning' => 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                                                                    'danger'  => 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                                                    'gray'    => 'bg-slate-200 text-slate-900 dark:bg-slate-600 dark:text-slate-100',
                                                                    default   => 'bg-slate-200 text-slate-900 dark:bg-slate-600 dark:text-slate-100'
                                                                } }}">
                                                                <span class="font-medium">{{ $b['label'] }}</span>
                                                                <span class="font-bold border-l border-current pl-1.5 ml-1.5 opacity-75">{{ $b['qty'] }}</span>
                                                            </button>
                                                        @endforeach
                                                    </div>
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

            <div class="mt-8">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Detalle de Items (Consolidado)</h3>
                        @if($this->detalleConsolidadoArticuloSeleccionado && ($this->detalleConsolidadoEstadoSeleccionadoLabel || $this->detalleConsolidadoDisponibilidadSeleccionadaLabel))
                            <p class="text-xs text-gray-600 dark:text-gray-300 mt-1">
                                Filtro activo: <strong>{{ $this->detalleConsolidadoArticuloSeleccionado->nombre }}</strong>
                                @if($this->detalleConsolidadoSedeSeleccionada)
                                    en <strong>{{ $this->detalleConsolidadoSedeSeleccionada->nombre }}</strong>
                                @endif
                                @if($this->detalleConsolidadoEstadoSeleccionadoLabel)
                                    + Estado <strong>{{ $this->detalleConsolidadoEstadoSeleccionadoLabel }}</strong>
                                @endif
                                @if($this->detalleConsolidadoDisponibilidadSeleccionadaLabel)
                                    + Disponibilidad <strong>{{ $this->detalleConsolidadoDisponibilidadSeleccionadaLabel }}</strong>
                                @endif
                            </p>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        @if($this->detalleConsolidadoArticuloSeleccionado || $this->detalleConsolidadoEstadoSeleccionadoLabel || ($this->detalleConsolidadoDisponibilidad && $this->detalleConsolidadoDisponibilidad !== 'en_uso'))
                            <button
                                wire:click="limpiarFiltroDetalleConsolidado"
                                type="button"
                                class="px-3 py-1 bg-amber-100 text-amber-800 text-xs rounded-full hover:bg-amber-200 dark:bg-amber-900/30 dark:text-amber-300"
                            >
                                Limpiar filtro rápido
                            </button>
                        @endif
                        <span class="px-3 py-1 bg-blue-50 text-blue-700 text-xs rounded-full dark:bg-blue-900/30 dark:text-blue-300">
                            Disponibilidad: {{ $this->detalleConsolidadoDisponibilidadSeleccionadaLabel ?? 'Todas' }}
                        </span>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    {{ $this->table }}
                </div>
            </div>
        </div>
    @endif

    <script>
        const APP_PUBLIC_URL = @js(rtrim((string) config('app.public_url', ''), '/'));
        let qrScriptPromise = null;

        function ensureQrLib() {
            if (window.QRCode && typeof window.QRCode.toCanvas === 'function') {
                return Promise.resolve(window.QRCode);
            }

            if (qrScriptPromise) {
                return qrScriptPromise;
            }

            qrScriptPromise = new Promise((resolve, reject) => {
                const existing = document.getElementById('codex-qrcode-lib-reportes');

                if (existing) {
                    existing.addEventListener('load', () => resolve(window.QRCode), { once: true });
                    existing.addEventListener('error', () => reject(new Error('No se pudo cargar QR')), { once: true });
                    return;
                }

                const script = document.createElement('script');
                script.id = 'codex-qrcode-lib-reportes';
                script.src = 'https://cdn.jsdelivr.net/npm/qrcode@1.5.4/build/qrcode.min.js';
                script.async = true;
                script.onload = () => resolve(window.QRCode);
                script.onerror = () => reject(new Error('No se pudo cargar QR'));
                document.head.appendChild(script);
            });

            return qrScriptPromise;
        }

        function getFirmaModal() {
            let modal = document.getElementById('firma-link-modal');
            if (modal) {
                return modal;
            }

            modal = document.createElement('div');
            modal.id = 'firma-link-modal';
            modal.style.cssText = 'position:fixed; inset:0; z-index:9999; display:none; align-items:center; justify-content:center; padding:18px; background:rgba(2,6,23,.72);';
            modal.innerHTML = `
                <div style="width:min(920px, 100%); border-radius:14px; border:1px solid #334155; background:#0f172a; color:#e2e8f0; box-shadow:0 20px 50px rgba(0,0,0,.45);">
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:14px 16px; border-bottom:1px solid #334155;">
                        <div style="font-size:15px; font-weight:700;">Enlace de firma generado</div>
                        <button type="button" id="firma-link-modal-close" style="border:none; border-radius:8px; background:#1e293b; color:#cbd5e1; width:30px; height:30px; cursor:pointer; font-size:18px; line-height:1;">×</button>
                    </div>
                    <div style="padding:16px;">
                        <div id="firma-link-meta" style="font-size:13px; color:#93c5fd; margin-bottom:12px;"></div>
                        <div style="display:flex; gap:14px; flex-wrap:wrap; align-items:flex-start;">
                            <div style="flex:0 0 240px; border:1px solid #334155; border-radius:10px; background:#020617; padding:10px; text-align:center;">
                                <canvas id="firma-link-qr-canvas" width="220" height="220" style="display:block; width:220px; height:220px; margin:0 auto; background:#fff; border-radius:8px;"></canvas>
                                <img id="firma-link-qr-image" alt="QR firma inventario" style="display:none; width:220px; height:220px; margin:0 auto; background:#fff; border-radius:8px;" />
                                <div id="firma-link-qr-status" style="margin-top:8px; font-size:11px; color:#94a3b8;">Escanea con la cámara de la tablet/celular.</div>
                            </div>
                            <div style="flex:1 1 420px;">
                                <textarea id="firma-link-url" readonly rows="4" style="width:100%; border:1px solid #475569; border-radius:10px; background:#020617; color:#e2e8f0; padding:10px; font-size:12px; line-height:1.45; resize:vertical;"></textarea>
                                <div style="margin-top:10px; display:flex; flex-wrap:wrap; gap:8px; align-items:center;">
                                    <button type="button" id="firma-link-copy" style="border:none; border-radius:8px; padding:8px 12px; background:#f59e0b; color:#111827; font-weight:700; cursor:pointer;">Copiar enlace</button>
                                    <a id="firma-link-open" href="#" target="_blank" rel="noopener noreferrer" style="display:inline-block; border-radius:8px; padding:8px 12px; background:#1d4ed8; color:#f8fafc; font-weight:700; text-decoration:none;">Abrir enlace</a>
                                    <span id="firma-link-copy-status" style="font-size:12px; color:#93c5fd;"></span>
                                </div>
                                <div style="margin-top:10px; font-size:12px; color:#94a3b8;">
                                    Si el QR no abre por red, use "Copiar enlace" y ábralo en la tablet.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            document.body.appendChild(modal);

            const closeModal = () => {
                modal.style.display = 'none';
            };

            modal.addEventListener('click', (event) => {
                if (event.target === modal) {
                    closeModal();
                }
            });

            const closeBtn = modal.querySelector('#firma-link-modal-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', closeModal);
            }

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && modal.style.display !== 'none') {
                    closeModal();
                }
            });

            return modal;
        }

        async function showFirmaModal({ firmaUrl, codigo, emailDestino, message }) {
            const modal = getFirmaModal();
            const meta = modal.querySelector('#firma-link-meta');
            const input = modal.querySelector('#firma-link-url');
            const openBtn = modal.querySelector('#firma-link-open');
            const copyBtn = modal.querySelector('#firma-link-copy');
            const copyStatus = modal.querySelector('#firma-link-copy-status');
            const canvas = modal.querySelector('#firma-link-qr-canvas');
            const image = modal.querySelector('#firma-link-qr-image');
            const qrStatus = modal.querySelector('#firma-link-qr-status');

            input.value = firmaUrl || '';
            openBtn.href = firmaUrl || '#';
            meta.textContent = `Código: ${codigo || 'N/A'}${emailDestino ? ` | Destino: ${emailDestino}` : ''}${message ? ` | ${message}` : ''}`;
            copyStatus.textContent = '';
            qrStatus.textContent = 'Escanea con la cámara de la tablet/celular.';
            canvas.style.display = 'block';
            image.style.display = 'none';
            image.src = '';
            modal.style.display = 'flex';

            copyBtn.onclick = async () => {
                try {
                    await navigator.clipboard.writeText(input.value || '');
                    copyStatus.textContent = 'Enlace copiado.';
                    setTimeout(() => { copyStatus.textContent = ''; }, 2200);
                } catch (_) {
                    prompt('Copie este enlace:', input.value || '');
                }
            };

            if (!firmaUrl) {
                qrStatus.textContent = 'No se encontró URL de firma. Use token o regenere el enlace.';
                return;
            }

            const showImageFallback = () => {
                image.src = `https://quickchart.io/qr?size=220&margin=1&text=${encodeURIComponent(firmaUrl)}`;
                image.style.display = 'block';
                canvas.style.display = 'none';
                qrStatus.textContent = 'QR generado por fallback. Escanee desde la tablet.';
            };

            try {
                await ensureQrLib();
                if (!window.QRCode || typeof window.QRCode.toCanvas !== 'function') {
                    showImageFallback();
                    return;
                }

                window.QRCode.toCanvas(canvas, firmaUrl, {
                    width: 220,
                    margin: 1,
                    color: {
                        dark: '#0f172a',
                        light: '#ffffff',
                    },
                }, (err) => {
                    if (err) {
                        showImageFallback();
                        return;
                    }

                    qrStatus.textContent = 'Escanea con la cámara de la tablet/celular.';
                });
            } catch (_) {
                showImageFallback();
            }
        }

        async function generarEnlaceFirma(url, btn) {
            if (!confirm('¿Generar enlace para firma en tablet/celular?')) return;
            
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = `<svg class="animate-spin h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Generando...`;
            
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                });
                
                let data = null;
                const contentType = response.headers.get('content-type') || '';
                
                if (contentType.includes('application/json')) {
                    data = await response.json();
                } else {
                    const raw = await response.text();
                    throw new Error(raw || `HTTP ${response.status}`);
                }
                
                if (data.success) {
                    btn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                    btn.classList.add('bg-green-600');
                    btn.innerHTML = `✅ Link listo`;

                    const firmaRuta = data.ruta_firma || '';
                    const firmaUrl = data.url_firma || (firmaRuta && APP_PUBLIC_URL ? `${APP_PUBLIC_URL}${firmaRuta}` : '');
                    const token = data.token || '';
                    const codigo = data.codigo_envio || '';
                    const emailDestino = data.email_destino || '';
                    const fallbackUrl = token && APP_PUBLIC_URL ? `${APP_PUBLIC_URL}/inventario/aprobar/${token}` : '';
                    const finalUrl = firmaUrl || fallbackUrl;
                    await showFirmaModal({
                        firmaUrl: finalUrl,
                        codigo: codigo,
                        emailDestino: emailDestino,
                        message: data.message || '',
                    });

                    setTimeout(() => {
                        btn.classList.remove('bg-green-600');
                        btn.classList.add('bg-blue-600', 'hover:bg-blue-700');
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    }, 3000);
                } else {
                    alert('Error: ' + data.message);
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            } catch (error) {
                alert('Error al generar el enlace: ' + (error.message || 'Intente de nuevo.'));
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }
    </script>
    </div>
</x-filament-panels::page>
