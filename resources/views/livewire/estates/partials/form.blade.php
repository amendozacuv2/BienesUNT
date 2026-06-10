<form wire:submit="save">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-box-open mr-1"></i>
                {{ $formTitle }}
            </h3>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="estate-area">Área</label>
                        <select
                            id="estate-area"
                            class="form-control @error('areaId') is-invalid @enderror"
                            wire:model.live="areaId"
                        >
                            <option value="">Seleccione un área</option>

                            @foreach ($areas as $area)
                                <option value="{{ $area->id }}">
                                    {{ $area->name }}
                                </option>
                            @endforeach
                        </select>

                        @error('areaId')
                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="estate-location">Ubicación</label>
                        <select
                            id="estate-location"
                            class="form-control @error('locationId') is-invalid @enderror"
                            wire:model="locationId"
                            @disabled($areaId === '')
                        >
                            <option value="">
                                {{ $areaId === '' ? 'Seleccione primero un área' : 'Seleccione una ubicación' }}
                            </option>

                            @foreach ($locations as $location)
                                <option value="{{ $location->id }}">
                                    {{ $location->name }}
                                </option>
                            @endforeach
                        </select>

                        @error('locationId')
                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
            </div>

            @if ($selectedArea)
                <div class="alert alert-info">
                    <strong>Encargado del área:</strong>

                    @forelse ($selectedArea->employees as $employee)
                        <span class="badge badge-light ml-1">
                            {{ $employee->full_name }}
                        </span>
                    @empty
                        <span class="ml-1">Sin encargado activo asignado.</span>
                    @endforelse
                </div>
            @endif

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="estate-patrimonial-code">Cod. patrimonial</label>
                        <input
                            type="text"
                            id="estate-patrimonial-code"
                            class="form-control @error('patrimonialCode') is-invalid @enderror"
                            wire:model.blur="patrimonialCode"
                            maxlength="100"
                            autocomplete="off"
                        >

                        @error('patrimonialCode')
                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="estate-internal-code">Cod. interno</label>
                        <input
                            type="text"
                            id="estate-internal-code"
                            class="form-control @error('internalCode') is-invalid @enderror"
                            wire:model.blur="internalCode"
                            maxlength="100"
                            autocomplete="off"
                        >

                        @error('internalCode')
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
                            'model' => $field,
                            'errorKey' => $field,
                            'inputId' => 'estate-edit-' . $field,
                            'maxlength' => $selectField['maxlength'],
                            'currentValue' => ${$field} ?? '',
                            'placeholder' => 'Buscar o escribir ' . mb_strtolower($selectField['label']),
                        ])
                    </div>
                @endforeach

                <div class="col-md-6">
                    @include('livewire.estates.partials.select2-field', [
                        'field' => 'series',
                        'label' => 'Serie',
                        'model' => 'series',
                        'errorKey' => 'series',
                        'inputId' => 'estate-edit-series',
                        'currentValue' => $series,
                        'mode' => 'local',
                        'placeholder' => 'Seleccione o escriba una serie',
                    ])
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="estate-situation">Situación</label>
                        <select
                            id="estate-situation"
                            class="form-control @error('situation') is-invalid @enderror"
                            wire:model="situation"
                        >
                            @foreach ($situations as $item)
                                <option value="{{ $item }}">{{ $item }}</option>
                            @endforeach
                        </select>

                        @error('situation')
                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="estate-conservation-status">Conservación</label>
                        <select
                            id="estate-conservation-status"
                            class="form-control @error('conservationStatus') is-invalid @enderror"
                            wire:model="conservationStatus"
                        >
                            @foreach ($conservationStatuses as $item)
                                <option value="{{ $item }}">{{ $item }}</option>
                            @endforeach
                        </select>

                        @error('conservationStatus')
                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    @include('livewire.estates.partials.select2-field', [
                        'field' => 'dimensions',
                        'label' => 'Dimensiones',
                        'model' => 'dimensions',
                        'errorKey' => 'dimensions',
                        'inputId' => 'estate-edit-dimensions',
                        'currentValue' => $dimensions,
                        'mode' => 'local',
                        'placeholder' => 'Seleccione o escriba dimensiones',
                    ])
                </div>

                <div class="col-md-6">
                    @include('livewire.estates.partials.select2-field', [
                        'field' => 'others',
                        'label' => 'Otros',
                        'model' => 'others',
                        'errorKey' => 'others',
                        'inputId' => 'estate-edit-others',
                        'currentValue' => $others,
                        'mode' => 'local',
                        'placeholder' => 'Seleccione o escriba otro valor',
                    ])
                </div>

                <div class="col-12">
                    <div class="form-group mb-0">
                        <label for="estate-observation">Observación</label>
                        <textarea
                            id="estate-observation"
                            class="form-control @error('observation') is-invalid @enderror"
                            wire:model.blur="observation"
                            rows="2"
                        ></textarea>

                        @error('observation')
                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer">
            <button
                type="submit"
                class="btn btn-primary"
                wire:loading.attr="disabled"
                wire:target="save"
            >
                <i class="fas fa-save mr-1"></i>
                {{ $submitLabel }}
            </button>

            <a href="{{ route('estates.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i>
                Volver
            </a>
        </div>
    </div>
</form>
