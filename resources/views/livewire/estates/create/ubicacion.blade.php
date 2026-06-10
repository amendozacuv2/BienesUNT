<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-map-marker-alt mr-1"></i>
            Ubicación destino
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

            <div class="col-12">
                @if ($selectedArea)
                    <div class="alert alert-info mb-0">
                        <strong>Encargado del área:</strong>

                        @forelse ($selectedArea->employees as $employee)
                            <span class="badge badge-light ml-1">
                                {{ $employee->full_name }}
                            </span>
                        @empty
                            <span class="ml-1">Sin encargado activo asignado.</span>
                        @endforelse
                    </div>
                @else
                    <div class="alert alert-light mb-0">
                        Selecciona un área para ver sus ubicaciones disponibles.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>