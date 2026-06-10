<div>
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-layer-group mr-1"></i>
                        Listado de areas
                    </h3>

                    @can('create.area')
                        <div class="card-tools">
                            <a href="{{ route('areas.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus-circle mr-1"></i>
                                Nueva area
                            </a>
                        </div>
                    @endcan
                </div>

                <div class="card-body">
                    <div class="form-group">
                        <label for="area-search">Buscar area</label>
                        <input
                            type="text"
                            id="area-search"
                            class="form-control"
                            placeholder="Buscar por nombre del area"
                            wire:model.live.debounce.400ms="search"
                            autocomplete="off"
                        >
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th style="width: 140px;">Estado</th>
                                    <th style="width: 160px;">Usuarios</th>
                                    <th style="width: 160px;">Localidades</th>
                                    <th style="width: 230px;">Acciones</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($areas as $area)
                                    <tr wire:key="area-row-{{ $area->id }}">
                                        <td class="align-middle">
                                            {{ $area->name }}
                                        </td>

                                        <td class="align-middle">
                                            @if ($area->is_active)
                                                <span class="badge badge-success">ACTIVO</span>
                                            @else
                                                <span class="badge badge-danger">INACTIVO</span>
                                            @endif
                                        </td>

                                        <td class="align-middle">
                                            {{ $area->users_count }}
                                        </td>

                                        <td class="align-middle">
                                            {{ $area->locations_count }}
                                        </td>

                                        <td class="align-middle">
                                            @can('edit.area')
                                                <a
                                                    href="{{ route('areas.edit', $area) }}"
                                                    class="btn btn-warning btn-sm"
                                                >
                                                    <i class="fas fa-edit mr-1"></i>
                                                    Editar
                                                </a>
                                            @endcan

                                            @can('destroy.area')
                                                <button
                                                    type="button"
                                                    class="btn btn-danger btn-sm"
                                                    wire:click="destroy({{ $area->id }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="destroy({{ $area->id }})"
                                                >
                                                    <i class="fas fa-trash-alt mr-1"></i>
                                                    Eliminar
                                                </button>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-muted">
                                            No se encontraron areas registradas.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($areas->hasPages())
                    <div class="card-footer">
                        {{ $areas->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
