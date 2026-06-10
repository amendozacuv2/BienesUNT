<div>
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-shield mr-1"></i>
                        Listado de roles
                    </h3>

                    @can('create.role')
                        <div class="card-tools">
                            <a href="{{ route('roles.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus-circle mr-1"></i>
                                Nuevo rol
                            </a>
                        </div>
                    @endcan
                </div>

                <div class="card-body">
                    <div class="form-group">
                        <label for="role-search">Buscar rol</label>
                        <input
                            type="text"
                            id="role-search"
                            class="form-control"
                            placeholder="Buscar por nombre del rol"
                            wire:model.live.debounce.400ms="search"
                        >
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th style="width: 220px;">Cantidad de permisos</th>
                                    <th style="width: 220px;">Acciones</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($roles as $role)
                                    <tr wire:key="role-row-{{ $role->id }}">
                                        <td class="align-middle">
                                            {{ $role->name }}
                                        </td>

                                        <td class="align-middle">
                                            {{ $role->permissions_count }}
                                        </td>

                                        <td class="align-middle">
                                            @can('edit.role')
                                                <a
                                                    href="{{ route('roles.edit', $role) }}"
                                                    class="btn btn-warning btn-sm"
                                                >
                                                    <i class="fas fa-edit mr-1"></i>
                                                    Editar
                                                </a>
                                            @endcan

                                            @can('destroy.role')
                                                @if ($role->users_count === 0)
                                                    <button
                                                        type="button"
                                                        class="btn btn-danger btn-sm"
                                                        wire:click="destroy({{ $role->id }})"
                                                        wire:loading.attr="disabled"
                                                        wire:target="destroy({{ $role->id }})"
                                                    >
                                                        <i class="fas fa-trash-alt mr-1"></i>
                                                        Eliminar
                                                    </button>
                                                @endif
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-muted">
                                            No se encontraron roles registrados.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($roles->hasPages())
                    <div class="card-footer">
                        {{ $roles->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
