<div class="card card-outline card-warning">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-search mr-1"></i>
            Buscar bien existente
        </h3>
    </div>

    <div class="card-body">
        @can('edit.estate')
            <div class="form-group">
                <label for="existing-estate-search">Buscar para actualizar</label>
                <input
                    type="text"
                    id="existing-estate-search"
                    class="form-control"
                    placeholder="Código, denominación, marca, modelo o tipo"
                    wire:model.live.debounce.400ms="existingSearch"
                    autocomplete="off"
                >
            </div>

            <small class="form-text text-muted mb-3">
                Puedes agregar un bien existente al formulario y asignarlo a la ubicación destino seleccionada.
            </small>

            @if (mb_strlen(trim($existingSearch)) >= 2)

                <div wire:loading.remove wire:target="existingSearch,existingPerPage">
                    @if ($existingEstates->total() > 0)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="text-muted">
                                Resultados encontrados:
                                <strong>{{ $existingEstates->total() }}</strong>
                            </div>

                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-bordered table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 70px;">N°</th>
                                        <th style="width: 150px;">Cod. interno</th>
                                        <th style="width: 160px;">Cod. patrimonial</th>
                                        <th>Denominación</th>
                                        <th style="min-width: 260px;">Ubicación actual</th>
                                        <th style="width: 130px;">Acción</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($existingEstates as $estate)
                                        <tr wire:key="existing-estate-row-{{ $estate->uuid }}">
                                            <td class="align-middle">
                                                {{ $existingEstates->firstItem() + $loop->index }}
                                            </td>

                                            <td class="align-middle">
                                                <strong>{{ $estate->internal_code }}</strong>
                                            </td>

                                            <td class="align-middle">
                                                {{ $estate->patrimonial_code ?: '—' }}
                                            </td>

                                            <td class="align-middle">
                                                {{ $estate->denomination ?: 'Sin denominación' }}

                                                @if ($estate->brand || $estate->model || $estate->type)
                                                    <small class="text-muted d-block">
                                                        {{ collect([$estate->brand, $estate->model, $estate->type])->filter()->implode(' / ') }}
                                                    </small>
                                                @endif
                                            </td>

                                            <td class="align-middle">
                                                <span class="d-block">
                                                    {{ $estate->location?->area?->name ?: 'Sin área' }}
                                                </span>
                                                <small class="text-muted">
                                                    {{ $estate->location?->name ?: 'Sin ubicación' }}
                                                </small>
                                            </td>

                                            <td class="align-middle">
                                                <button
                                                    type="button"
                                                    class="btn btn-warning btn-sm"
                                                    wire:click="addExistingEstate('{{ $estate->uuid }}')"
                                                    wire:loading.attr="disabled"
                                                    wire:target="addExistingEstate('{{ $estate->uuid }}')"
                                                >
                                                    <i class="fas fa-plus-circle mr-1"></i>
                                                    Agregar
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if ($existingEstates->hasPages())
                            <div class="mt-3">
                                {{ $existingEstates->links() }}
                            </div>
                        @endif
                    @else
                        <div class="alert alert-light mb-0">
                            No se encontraron bienes con ese criterio.
                        </div>
                    @endif
                </div>
            @else
                <div class="alert alert-light mb-0">
                    Ingresa al menos 2 caracteres para buscar.
                </div>
            @endif
        @else
            <div class="alert alert-light mb-0">
                Tu usuario puede registrar bienes nuevos. Para actualizar bienes existentes se requiere permiso de edición.
            </div>
        @endcan
    </div>
</div>