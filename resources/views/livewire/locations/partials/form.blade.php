<form wire:submit="save">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-map-marker-alt mr-1"></i>
                {{ $formTitle }}
            </h3>
        </div>

        <div class="card-body">
            <div class="form-group">
                <label for="location-name">Nombre</label>
                <input
                    type="text"
                    id="location-name"
                    class="form-control @error('name') is-invalid @enderror"
                    placeholder="Ingrese el nombre de la ubicación"
                    wire:model.blur="name"
                    maxlength="150"
                    autocomplete="off"
                >

                @error('name')
                    <small class="text-danger d-block mt-1">
                        {{ $message }}
                    </small>
                @enderror
            </div>

            <div class="form-group">
                <label for="location-area">Área</label>
                <select
                    id="location-area"
                    class="form-control @error('areaId') is-invalid @enderror"
                    wire:model="areaId"
                >
                    <option value="">Seleccione un área</option>

                    @foreach ($areas as $area)
                        <option value="{{ $area->id }}">
                            {{ $area->name }}
                        </option>
                    @endforeach
                </select>

                @error('areaId')
                    <small class="text-danger d-block mt-1">
                        {{ $message }}
                    </small>
                @enderror
            </div>

            <div class="form-group mb-0">
                <label for="location-status">Estado</label>
                <select
                    id="location-status"
                    class="form-control @error('isActive') is-invalid @enderror"
                    wire:model="isActive"
                >
                    <option value="1">ACTIVO</option>
                    <option value="0">INACTIVO</option>
                </select>

                @error('isActive')
                    <small class="text-danger d-block mt-1">
                        {{ $message }}
                    </small>
                @enderror
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

            <a href="{{ route('locations.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i>
                Volver
            </a>
        </div>
    </div>
</form>
