<div>
    <form wire:submit="save">
        <div class="row">
            <div class="col-12">
                @include('livewire.estates.create.ubicacion')
            </div>

            <div class="col-12">
                @include('livewire.estates.create.searchestates')
            </div>

            <div class="col-12">
                <div class="card card-outline card-success">
                    @include('livewire.estates.create.estatescreate')

                    <div class="card-footer">
                        <button
                            type="submit"
                            class="btn btn-primary"
                            wire:loading.attr="disabled"
                            wire:target="save"
                        >
                            <i class="fas fa-save mr-1"></i>
                            Guardar bienes
                        </button>

                        <button
                            type="button"
                            class="btn btn-success"
                            wire:click="addBlankRow"
                            wire:loading.attr="disabled"
                            wire:target="addBlankRow"
                        >
                            <i class="fas fa-plus-circle mr-1"></i>
                            Agregar otro bien
                        </button>

                        <a href="{{ route('estates.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i>
                            Volver
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>