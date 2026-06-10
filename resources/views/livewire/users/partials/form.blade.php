<form wire:submit="save">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-user-cog mr-1"></i>
                {{ $formTitle }}
            </h3>
        </div>

        <div class="card-body">
            <div class="form-group">
                <label for="user-name">Nombre</label>
                <input
                    type="text"
                    id="user-name"
                    class="form-control @error('name') is-invalid @enderror"
                    placeholder="Ingrese el nombre del usuario"
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
                <label for="user-username">Nombre de usuario</label>
                <input
                    type="text"
                    id="user-username"
                    class="form-control @error('username') is-invalid @enderror"
                    placeholder="Ingrese el nombre de usuario"
                    wire:model.blur="username"
                    maxlength="100"
                    autocomplete="off"
                >

                @error('username')
                    <small class="text-danger d-block mt-1">
                        {{ $message }}
                    </small>
                @enderror
            </div>

            <div class="form-group">
                <label for="user-password">
                    Contraseña
                </label>

                <input
                    type="password"
                    id="user-password"
                    class="form-control @error('password') is-invalid @enderror"
                    placeholder="{{ $isEditing ? 'Dejar vacío para mantener la contraseña actual' : 'Ingrese la contraseña' }}"
                    wire:model.defer="password"
                    autocomplete="new-password"
                >

                @if ($isEditing)
                    <small class="form-text text-muted">
                        Solo completa este campo si deseas cambiar la contraseña.
                    </small>
                @endif

                @error('password')
                    <small class="text-danger d-block mt-1">
                        {{ $message }}
                    </small>
                @enderror
            </div>

            <div class="form-group">
                <label for="user-password-confirmation">Confirmar contraseña</label>
                <input
                    type="password"
                    id="user-password-confirmation"
                    class="form-control @error('passwordConfirmation') is-invalid @enderror"
                    placeholder="{{ $isEditing ? 'Confirma la nueva contraseña' : 'Confirme la contraseña' }}"
                    wire:model.defer="passwordConfirmation"
                    autocomplete="new-password"
                >

                @error('passwordConfirmation')
                    <small class="text-danger d-block mt-1">
                        {{ $message }}
                    </small>
                @enderror
            </div>

            <div class="form-group">
                <label for="user-role">Rol</label>
                <select
                    id="user-role"
                    class="form-control @error('roleId') is-invalid @enderror"
                    wire:model="roleId"
                >
                    <option value="">Seleccione un rol</option>

                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}">
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>

                @error('roleId')
                    <small class="text-danger d-block mt-1">
                        {{ $message }}
                    </small>
                @enderror
            </div>

            <div class="form-group">
                <label>Áreas</label>

                <div class="border rounded p-3">
                    @forelse ($areas as $area)
                        <div
                            class="custom-control custom-checkbox mb-2"
                            wire:key="user-area-option-{{ $area->id }}"
                        >
                            <input
                                type="checkbox"
                                id="user-area-{{ $area->id }}"
                                class="custom-control-input"
                                value="{{ $area->id }}"
                                wire:model.live="selectedAreas"
                            >

                            <label
                                for="user-area-{{ $area->id }}"
                                class="custom-control-label"
                            >
                                {{ $area->name }}
                            </label>
                        </div>
                    @empty
                        <p class="text-muted mb-0">
                            No existen áreas disponibles.
                        </p>
                    @endforelse
                </div>

                @error('selectedAreas')
                    <small class="text-danger d-block mt-2">
                        {{ $message }}
                    </small>
                @enderror

                @error('selectedAreas.*')
                    <small class="text-danger d-block mt-2">
                        {{ $message }}
                    </small>
                @enderror
            </div>

            <div class="form-group mb-0">
                <label for="user-status">Estado</label>
                <select
                    id="user-status"
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

            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i>
                Volver
            </a>
        </div>
    </div>
</form>
