<div class="card-header">
    <h3 class="card-title">
        <i class="fas fa-box-open mr-1"></i>
        Bienes para registrar o actualizar
    </h3>
</div>

<div class="card-body">
    @error('rows')
        <div class="alert alert-danger">{{ $message }}</div>
    @enderror

    <div id="estate-create-accordion">
        @foreach ($rows as $index => $row)
            @php
                $rowKey = $row['row_key'];
                $rowHasErrors = $errors->has('rows.' . $index . '.*');
                $isOpen = (($openedRows[$rowKey] ?? ($index === 0)) || $rowHasErrors);
            @endphp

            <div
                class="card card-outline {{ ! empty($row['existing_uuid']) ? 'card-warning' : 'card-primary' }}"
                wire:key="estate-form-row-{{ $rowKey }}"
            >
                <div class="card-header">
                    <h3 class="card-title">
                        Bien {{ $index + 1 }}

                        @if (! empty($row['existing_uuid']))
                            <span class="badge badge-warning ml-1">Actualización</span>
                        @else
                            <span class="badge badge-primary ml-1">Nuevo</span>
                        @endif

                        @if ($rowHasErrors)
                            <span class="badge badge-danger ml-1">Revisar datos</span>
                        @endif
                    </h3>

                    <div class="card-tools">
                        <button
                            type="button"
                            class="btn btn-tool"
                            wire:click="toggleRow('{{ $rowKey }}')"
                            title="{{ $isOpen ? 'Ocultar' : 'Mostrar' }}"
                        >
                            <i class="fas {{ $isOpen ? 'fa-chevron-up' : 'fa-chevron-down' }}"></i>
                        </button>

                        <button
                            type="button"
                            class="btn btn-tool text-danger"
                            wire:click="removeRow({{ $index }})"
                            wire:loading.attr="disabled"
                            wire:target="removeRow({{ $index }})"
                            title="Quitar"
                        >
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                @if ($isOpen)
                    <div class="card-body">
                        @if (! empty($row['existing_uuid']))
                            <div class="alert alert-warning">
                                <strong>Ubicación actual:</strong>
                                {{ $row['current_area'] ?: 'Sin área' }} /
                                {{ $row['current_location'] ?: 'Sin ubicación' }}
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Cod. patrimonial</label>
                                    <input
                                        type="text"
                                        class="form-control @error('rows.' . $index . '.patrimonial_code') is-invalid @enderror"
                                        wire:model.blur="rows.{{ $index }}.patrimonial_code"
                                        maxlength="100"
                                        autocomplete="off"
                                    >

                                    @error('rows.' . $index . '.patrimonial_code')
                                        <small class="text-danger d-block mt-1">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Cod. interno</label>
                                    <input
                                        type="text"
                                        class="form-control @error('rows.' . $index . '.internal_code') is-invalid @enderror"
                                        wire:model.blur="rows.{{ $index }}.internal_code"
                                        maxlength="100"
                                        autocomplete="off"
                                    >

                                    @error('rows.' . $index . '.internal_code')
                                        <small class="text-danger d-block mt-1">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            @foreach ([
                                ['field' => 'denomination', 'label' => 'Denominación', 'maxlength' => 255],
                                ['field' => 'brand', 'label' => 'Marca', 'maxlength' => 150],
                                ['field' => 'model', 'label' => 'Modelo', 'maxlength' => 150],
                                ['field' => 'type', 'label' => 'Tipo', 'maxlength' => 150],
                                ['field' => 'color', 'label' => 'Color', 'maxlength' => 100],
                            ] as $selectField)
                                @php
                                    $field = $selectField['field'];
                                @endphp

                                <div class="col-md-6">
                                    @include('livewire.estates.partials.select2-field', [
                                        'field' => $field,
                                        'label' => $selectField['label'],
                                        'model' => 'rows.' . $index . '.' . $field,
                                        'errorKey' => 'rows.' . $index . '.' . $field,
                                        'inputId' => 'estate-row-' . $rowKey . '-' . $field,
                                        'maxlength' => $selectField['maxlength'],
                                        'currentValue' => $row[$field] ?? '',
                                        'placeholder' => 'Buscar o escribir ' . mb_strtolower($selectField['label']),
                                    ])
                                </div>
                            @endforeach

                            <div class="col-md-6">
                                @include('livewire.estates.partials.select2-field', [
                                    'field' => 'series',
                                    'label' => 'Serie',
                                    'model' => 'rows.' . $index . '.series',
                                    'errorKey' => 'rows.' . $index . '.series',
                                    'inputId' => 'estate-row-' . $rowKey . '-series',
                                    'currentValue' => $row['series'] ?? '',
                                    'mode' => 'local',
                                    'placeholder' => 'Seleccione o escriba una serie',
                                ])
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Situación</label>
                                    <select
                                        class="form-control @error('rows.' . $index . '.situation') is-invalid @enderror"
                                        wire:model="rows.{{ $index }}.situation"
                                    >
                                        @foreach ($situations as $item)
                                            <option value="{{ $item }}">{{ $item }}</option>
                                        @endforeach
                                    </select>

                                    @error('rows.' . $index . '.situation')
                                        <small class="text-danger d-block mt-1">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Conservación</label>
                                    <select
                                        class="form-control @error('rows.' . $index . '.conservation_status') is-invalid @enderror"
                                        wire:model="rows.{{ $index }}.conservation_status"
                                    >
                                        @foreach ($conservationStatuses as $item)
                                            <option value="{{ $item }}">{{ $item }}</option>
                                        @endforeach
                                    </select>

                                    @error('rows.' . $index . '.conservation_status')
                                        <small class="text-danger d-block mt-1">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                @include('livewire.estates.partials.select2-field', [
                                    'field' => 'dimensions',
                                    'label' => 'Dimensiones',
                                    'model' => 'rows.' . $index . '.dimensions',
                                    'errorKey' => 'rows.' . $index . '.dimensions',
                                    'inputId' => 'estate-row-' . $rowKey . '-dimensions',
                                    'currentValue' => $row['dimensions'] ?? '',
                                    'mode' => 'local',
                                    'placeholder' => 'Seleccione o escriba dimensiones',
                                ])
                            </div>

                            <div class="col-md-6">
                                @include('livewire.estates.partials.select2-field', [
                                    'field' => 'others',
                                    'label' => 'Otros',
                                    'model' => 'rows.' . $index . '.others',
                                    'errorKey' => 'rows.' . $index . '.others',
                                    'inputId' => 'estate-row-' . $rowKey . '-others',
                                    'currentValue' => $row['others'] ?? '',
                                    'mode' => 'local',
                                    'placeholder' => 'Seleccione o escriba otro valor',
                                ])
                            </div>

                            <div class="col-12">
                                <div class="form-group mb-0">
                                    <label>Observación</label>
                                    <textarea
                                        class="form-control @error('rows.' . $index . '.observation') is-invalid @enderror"
                                        wire:model.blur="rows.{{ $index }}.observation"
                                        rows="1"
                                    ></textarea>

                                    @error('rows.' . $index . '.observation')
                                        <small class="text-danger d-block mt-1">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
