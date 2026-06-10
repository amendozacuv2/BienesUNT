<form wire:submit="save">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-user-tie mr-1"></i>
                {{ $formTitle }}
            </h3>
        </div>

        <div class="card-body">
            <div class="form-group">
                <label for="employee-dni">DNI</label>
                <input
                    type="text"
                    id="employee-dni"
                    class="form-control @error('dni') is-invalid @enderror"
                    placeholder="Ingrese el DNI del empleado"
                    wire:model.live.debounce.500ms="dni"
                    maxlength="8"
                    inputmode="numeric"
                    autocomplete="off"
                >

                <small class="form-text text-muted">
                    Al ingresar los 8 dígitos, intentaremos completar los datos automáticamente.
                </small>

                <small
                    class="form-text text-muted"
                    wire:loading
                    wire:target="dni"
                >
                    Consultando DNI...
                </small>

                @error('dni')
                    <small class="text-danger d-block mt-1">
                        {{ $message }}
                    </small>
                @enderror
            </div>

            <div class="form-group">
                <label for="employee-name">Nombres</label>
                <input
                    type="text"
                    id="employee-name"
                    class="form-control @error('name') is-invalid @enderror"
                    placeholder="Ingrese los nombres del empleado"
                    wire:model.blur="name"
                    maxlength="120"
                    autocomplete="off"
                    @disabled(! $identityFieldsEnabled)
                >

                @if (! $identityFieldsEnabled)
                    <small class="form-text text-muted">
                        Primero ingresa un DNI válido de 8 dígitos.
                    </small>
                @endif

                @error('name')
                    <small class="text-danger d-block mt-1">
                        {{ $message }}
                    </small>
                @enderror
            </div>

            <div class="form-group">
                <label for="employee-lastname">Apellidos</label>
                <input
                    type="text"
                    id="employee-lastname"
                    class="form-control @error('lastname') is-invalid @enderror"
                    placeholder="Ingrese los apellidos del empleado"
                    wire:model.blur="lastname"
                    maxlength="120"
                    autocomplete="off"
                    @disabled(! $identityFieldsEnabled)
                >

                @error('lastname')
                    <small class="text-danger d-block mt-1">
                        {{ $message }}
                    </small>
                @enderror
            </div>

            <div class="form-group">
                <label for="employee-area">Área asignada</label>
                <select
                    id="employee-area"
                    class="form-control @error('areaId') is-invalid @enderror"
                    wire:model="areaId"
                >
                    <option value="">Sin área asignada</option>

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
                <label for="employee-status">Estado</label>
                <select
                    id="employee-status"
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
                wire:target="save,dni"
            >
                <i class="fas fa-save mr-1"></i>
                {{ $submitLabel }}
            </button>

            <a href="{{ route('employees.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i>
                Volver
            </a>
        </div>
    </div>
</form>
