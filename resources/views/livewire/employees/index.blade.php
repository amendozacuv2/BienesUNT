<div>
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-users mr-1"></i>
                        Listado de empleados
                    </h3>

                    @can('create.employee')
                        <div class="card-tools">
                            <a href="{{ route('employees.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus-circle mr-1"></i>
                                Nuevo empleado
                            </a>
                        </div>
                    @endcan
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="employee-search">Buscar empleado</label>
                                <input
                                    type="text"
                                    id="employee-search"
                                    class="form-control"
                                    placeholder="Buscar por DNI, nombres o apellidos"
                                    wire:model.live.debounce.400ms="search"
                                    autocomplete="off"
                                >
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="employee-area-filter">Filtrar por área</label>
                                <select
                                    id="employee-area-filter"
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
                                    <th style="width: 130px;">DNI</th>
                                    <th>Nombres</th>
                                    <th>Apellidos</th>
                                    <th style="width: 260px;">Área asignada</th>
                                    <th style="width: 140px;">Estado</th>
                                    <th style="width: 230px;">Acciones</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($employees as $employee)
                                    <tr wire:key="employee-row-{{ $employee->id }}">
                                        <td class="align-middle">
                                            {{ $employee->dni }}
                                        </td>

                                        <td class="align-middle">
                                            {{ $employee->name }}
                                        </td>

                                        <td class="align-middle">
                                            {{ $employee->lastname }}
                                        </td>

                                        <td class="align-middle">
                                            @forelse ($employee->areas as $area)
                                                <span class="badge badge-info">
                                                    {{ $area->name }}
                                                </span>
                                            @empty
                                                <span class="text-muted">Sin área asignada</span>
                                            @endforelse
                                        </td>

                                        <td class="align-middle">
                                            @if ($employee->is_active)
                                                <span class="badge badge-success">ACTIVO</span>
                                            @else
                                                <span class="badge badge-danger">INACTIVO</span>
                                            @endif
                                        </td>

                                        <td class="align-middle">
                                            @can('edit.employee')
                                                <a
                                                    href="{{ route('employees.edit', $employee) }}"
                                                    class="btn btn-warning btn-sm"
                                                >
                                                    <i class="fas fa-edit mr-1"></i>
                                                    Editar
                                                </a>
                                            @endcan

                                            @can('destroy.employee')
                                                <button
                                                    type="button"
                                                    class="btn btn-danger btn-sm"
                                                    wire:click="destroy({{ $employee->id }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="destroy({{ $employee->id }})"
                                                >
                                                    <i class="fas fa-trash-alt mr-1"></i>
                                                    Eliminar
                                                </button>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-muted">
                                            No se encontraron empleados registrados.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($employees->hasPages())
                    <div class="card-footer">
                        {{ $employees->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
