<div>
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-boxes mr-1"></i>
                        Listado de bienes
                    </h3>

                    <div class="card-tools">
                        @can('export.estate')
                            <button
                                type="button"
                                class="btn btn-success btn-sm"
                                wire:click="requestExport"
                                wire:loading.attr="disabled"
                                wire:target="requestExport"
                            >
                                <span wire:loading.remove wire:target="requestExport">
                                    <i class="fas fa-file-excel mr-1"></i>
                                    Exportar
                                </span>
                                <span wire:loading wire:target="requestExport">
                                    <i class="fas fa-spinner fa-spin mr-1"></i>
                                    Enviando...
                                </span>
                            </button>
                        @endcan

                        @can('create.estate')
                            <a href="{{ route('estates.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus-circle mr-1"></i>
                                Nuevo registro
                            </a>
                        @endcan
                    </div>
                </div>

                <div class="card-body">
                    @can('export.estate')
                        <livewire:estates.export-status />
                    @endcan

                    @include('livewire.estates.partials.widgets', [
                        'widgets' => $widgets ?? [],
                    ])

                    <div class="row">
                        <div class="col-lg-4 col-md-6">
                            <div class="form-group">
                                <label for="estate-search">Buscar bien</label>
                                <input
                                    type="text"
                                    id="estate-search"
                                    class="form-control"
                                    placeholder="Código, denominación, marca, modelo, tipo u observación"
                                    wire:model.live.debounce.400ms="search"
                                    autocomplete="off"
                                >
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-6">
                            <div class="form-group">
                                <label for="estate-area-filter">Área</label>
                                <select
                                    id="estate-area-filter"
                                    class="form-control"
                                    wire:model.live="areaId"
                                >
                                    <option value="">Todas</option>

                                    @foreach ($areas as $area)
                                        <option value="{{ $area->id }}">
                                            {{ $area->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-6">
                            <div class="form-group">
                                <label for="estate-location-filter">Ubicación</label>
                                <select
                                    id="estate-location-filter"
                                    class="form-control"
                                    wire:model.live="locationId"
                                    @disabled($areaId === '')
                                >
                                    <option value="">
                                        {{ $areaId === '' ? 'Seleccione un área' : 'Todas' }}
                                    </option>

                                    @foreach ($locations as $location)
                                        <option value="{{ $location->id }}">
                                            {{ $location->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-6">
                            <div class="form-group">
                                <label for="estate-situation-filter">Situación</label>
                                <select
                                    id="estate-situation-filter"
                                    class="form-control"
                                    wire:model.live="situation"
                                >
                                    <option value="">Todas</option>

                                    @foreach ($situations as $item)
                                        <option value="{{ $item }}">
                                            {{ $item }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-6">
                            <div class="form-group">
                                <label for="estate-status-filter">Conservación</label>
                                <select
                                    id="estate-status-filter"
                                    class="form-control"
                                    wire:model.live="conservationStatus"
                                >
                                    <option value="">Todas</option>

                                    @foreach ($conservationStatuses as $item)
                                        <option value="{{ $item }}">
                                            {{ $item }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="text-muted">
                            Resultados encontrados: {{ $estates->total() }}
                        </div>

                        <div style="width: 110px;">
                            <select class="form-control form-control-sm" wire:model.live="perPage">
                                <option value="10">10 filas</option>
                                <option value="25">25 filas</option>
                                <option value="50">50 filas</option>
                            </select>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 150px;">Cod. patrimonial</th>
                                    <th style="width: 150px;">Cod. interno</th>
                                    <th style="min-width: 230px;">Ubicación</th>
                                    <th>Marca</th>
                                    <th>Modelo</th>
                                    <th>Tipo</th>
                                    <th style="width: 120px;">Situación</th>
                                    <th style="width: 160px;">Conservación</th>
                                    <th style="width: 210px;">Acciones</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($estates as $estate)
                                    <tr wire:key="estate-row-{{ $estate->uuid }}">
                                        <td class="align-middle">
                                            {{ $estate->patrimonial_code ?: '—' }}
                                        </td>

                                        <td class="align-middle">
                                            <strong>{{ $estate->internal_code }}</strong>
                                        </td>

                                        <td class="align-middle">
                                            @if ($estate->location)
                                                <span class="d-block">
                                                    {{ $estate->location->area?->name ?: 'Sin área' }}
                                                </span>
                                                <small class="text-muted">
                                                    {{ $estate->location->name ?: 'Sin ubicación' }}
                                                </small>
                                            @else
                                                <span class="text-muted">Sin ubicación</span>
                                            @endif
                                        </td>

                                        <td class="align-middle">
                                            {{ $estate->brand ?: '—' }}
                                        </td>

                                        <td class="align-middle">
                                            {{ $estate->model ?: '—' }}
                                        </td>

                                        <td class="align-middle">
                                            {{ $estate->type ?: '—' }}
                                        </td>

                                        <td class="align-middle">
                                            @if ($estate->situation === 'EN USO')
                                                <span class="badge badge-success">EN USO</span>
                                            @elseif ($estate->situation === 'DESUSO')
                                                <span class="badge badge-secondary">DESUSO</span>
                                            @else
                                                <span class="badge badge-light">SIN REGISTRO</span>
                                            @endif
                                        </td>

                                        <td class="align-middle">
                                            @if ($estate->conservation_status === 'BUENO')
                                                <span class="badge badge-success">BUENO</span>
                                            @elseif ($estate->conservation_status === 'REGULAR')
                                                <span class="badge badge-warning">REGULAR</span>
                                            @elseif ($estate->conservation_status === 'MALO')
                                                <span class="badge badge-danger">MALO</span>
                                            @else
                                                <span class="badge badge-light">SIN REGISTRO</span>
                                            @endif
                                        </td>

                                        <td class="align-middle">
                                            @can('edit.estate')
                                                <a
                                                    href="{{ route('estates.edit', $estate->uuid) }}"
                                                    class="btn btn-warning btn-sm"
                                                >
                                                    <i class="fas fa-edit mr-1"></i>
                                                    Editar
                                                </a>
                                            @endcan

                                            @can('destroy.estate')
                                                <button
                                                    type="button"
                                                    class="btn btn-danger btn-sm"
                                                    wire:click="destroy('{{ $estate->uuid }}')"
                                                    wire:loading.attr="disabled"
                                                    wire:target="destroy('{{ $estate->uuid }}')"
                                                >
                                                    <i class="fas fa-trash-alt mr-1"></i>
                                                    Eliminar
                                                </button>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-muted">
                                            No se encontraron bienes registrados.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($estates->hasPages())
                    <div class="card-footer">
                        {{ $estates->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
