<div>
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-users-cog mr-1"></i>
                        Listado de usuarios
                    </h3>

                    @can('create.user')
                        <div class="card-tools">
                            <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus-circle mr-1"></i>
                                Nuevo usuario
                            </a>
                        </div>
                    @endcan
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="user-search">Buscar usuario</label>
                                <input
                                    type="text"
                                    id="user-search"
                                    class="form-control"
                                    placeholder="Buscar por nombre o nombre de usuario"
                                    wire:model.live.debounce.400ms="search"
                                    autocomplete="off"
                                >
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="user-area-filter">Filtrar por área</label>
                                <select
                                    id="user-area-filter"
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
                                    <th style="width: 90px;">ID</th>
                                    <th>Nombre</th>
                                    <th style="width: 220px;">Nombre de usuario</th>
                                    <th style="width: 170px;">Creado</th>
                                    <th style="width: 120px;">Áreas</th>
                                    <th style="width: 140px;">Estado</th>
                                    <th style="width: 230px;">Acciones</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($users as $user)
                                    <tr wire:key="user-row-{{ $user->id }}">
                                        <td class="align-middle">
                                            {{ $user->id }}
                                        </td>

                                        <td class="align-middle">
                                            {{ $user->name }}
                                        </td>

                                        <td class="align-middle">
                                            {{ $user->username }}
                                        </td>

                                        <td class="align-middle">
                                            {{ $user->created_at?->format('d/m/Y H:i') }}
                                        </td>

                                        <td class="align-middle">
                                            <span class="badge badge-info">
                                                {{ $user->active_areas_count }}
                                            </span>
                                        </td>

                                        <td class="align-middle">
                                            @if ($user->is_active)
                                                <span class="badge badge-success">ACTIVO</span>
                                            @else
                                                <span class="badge badge-danger">INACTIVO</span>
                                            @endif
                                        </td>

                                        <td class="align-middle">
                                            @can('edit.user')
                                                <a
                                                    href="{{ route('users.edit', $user) }}"
                                                    class="btn btn-warning btn-sm"
                                                >
                                                    <i class="fas fa-edit mr-1"></i>
                                                    Editar
                                                </a>
                                            @endcan

                                            @can('destroy.user')
                                                <button
                                                    type="button"
                                                    class="btn btn-danger btn-sm"
                                                    wire:click="destroy({{ $user->id }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="destroy({{ $user->id }})"
                                                >
                                                    <i class="fas fa-trash-alt mr-1"></i>
                                                    Eliminar
                                                </button>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-muted">
                                            No se encontraron usuarios registrados.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($users->hasPages())
                    <div class="card-footer">
                        {{ $users->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
