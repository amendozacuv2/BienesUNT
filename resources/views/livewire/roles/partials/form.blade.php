<form wire:submit="save">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-user-shield mr-1"></i>
                {{ $formTitle }}
            </h3>
        </div>

        <div class="card-body">
            <div class="form-group">
                <label for="role-name">Nombre</label>
                <input
                    type="text"
                    id="role-name"
                    class="form-control @error('name') is-invalid @enderror"
                    placeholder="Ingrese el nombre del rol"
                    wire:model.blur="name"
                    autocomplete="off"
                >

                @error('name')
                    <small class="text-danger d-block mt-1">
                        {{ $message }}
                    </small>
                @enderror
            </div>

            <div class="form-group mb-0">
                <label>Permisos</label>

                @error('selectedPermissions')
                    <small class="text-danger d-block mb-2">
                        {{ $message }}
                    </small>
                @enderror

                <div class="border rounded p-3">
                    @forelse ($permissions as $permission)
                        <div
                            class="custom-control custom-checkbox mb-2"
                            wire:key="permission-option-{{ $permission->id }}"
                        >
                            <input
                                type="checkbox"
                                id="permission-{{ $permission->id }}"
                                class="custom-control-input"
                                value="{{ $permission->id }}"
                                wire:model.live="selectedPermissions"
                            >

                            <label
                                for="permission-{{ $permission->id }}"
                                class="custom-control-label"
                            >
                                {{ $permission->description ?: 'Sin descripcion registrada' }}
                            </label>
                        </div>
                    @empty
                        <p class="text-muted mb-0">
                            No existen permisos registrados.
                        </p>
                    @endforelse
                </div>

                @error('selectedPermissions.*')
                    <small class="text-danger d-block mt-2">
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

            <a href="{{ route('roles.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i>
                Volver
            </a>
        </div>
    </div>
</form>
