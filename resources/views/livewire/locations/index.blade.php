<div>
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-map-marker-alt mr-1"></i>
                        Listado de ubicaciones
                    </h3>

                    @can('create.location')
                        <div class="card-tools">
                            <a href="{{ route('locations.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus-circle mr-1"></i>
                                Nueva ubicación
                            </a>
                        </div>
                    @endcan
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="location-search">Buscar ubicación</label>
                                <input
                                    type="text"
                                    id="location-search"
                                    class="form-control"
                                    placeholder="Buscar por nombre de la ubicación"
                                    wire:model.live.debounce.400ms="search"
                                    autocomplete="off"
                                >
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="location-area-filter">Filtrar por área</label>
                                <select
                                    id="location-area-filter"
                                    class="form-control"
                                    wire:model.live="areaId"
                                >
                                    <option value="">Todas las áreas</option>

                                    @foreach ($areas as $area)
                                        <option value="{{ $area->id }}">
                                            {{ $area->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th style="width: 260px;">Área</th>
                                    <th style="width: 140px;">Estado</th>
                                    <th style="width: 230px;">Acciones</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($locations as $location)
                                    <tr wire:key="location-row-{{ $location->id }}">
                                        <td class="align-middle">
                                            {{ $location->name }}
                                        </td>

                                        <td class="align-middle">
                                            {{ $location->area?->name ?? 'Sin área' }}
                                        </td>

                                        <td class="align-middle">
                                            @if ($location->is_active)
                                                <span class="badge badge-success">ACTIVO</span>
                                            @else
                                                <span class="badge badge-danger">INACTIVO</span>
                                            @endif
                                        </td>

                                        <td class="align-middle">
                                            @can('edit.location')
                                                <a
                                                    href="{{ route('locations.edit', $location) }}"
                                                    class="btn btn-warning btn-sm"
                                                >
                                                    <i class="fas fa-edit mr-1"></i>
                                                    Editar
                                                </a>
                                            @endcan

                                            @can('destroy.location')
                                                <button
                                                    type="button"
                                                    class="btn btn-danger btn-sm"
                                                    wire:click="destroy({{ $location->id }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="destroy({{ $location->id }})"
                                                >
                                                    <i class="fas fa-trash-alt mr-1"></i>
                                                    Eliminar
                                                </button>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-muted">
                                            No se encontraron ubicaciones registradas.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($locations->hasPages())
                    <div class="card-footer">
                        {{ $locations->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
