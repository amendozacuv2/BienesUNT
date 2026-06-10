<form wire:submit="save">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-layer-group mr-1"></i>
                {{ $formTitle }}
            </h3>
        </div>

        <div class="card-body">
            <div class="form-group">
                <label for="area-name">Nombre</label>
                <input
                    type="text"
                    id="area-name"
                    class="form-control @error('name') is-invalid @enderror"
                    placeholder="Ingrese el nombre del area"
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

            <div class="form-group mb-0">
                <label for="area-status">Estado</label>
                <select
                    id="area-status"
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

            <a href="{{ route('areas.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i>
                Volver
            </a>
        </div>
    </div>
</form>
