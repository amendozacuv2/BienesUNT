<div class="row">
    <div class="col-12">
        <div class="card card-outline card-primary">

            <div class="card-body">
                <div class="form-group">
                    <label for="profile-name">Nombre</label>
                    <input
                        type="text"
                        id="profile-name"
                        class="form-control"
                        value="{{ $name }}"
                        disabled
                    >
                </div>

                <div class="form-group">
                    <label for="profile-username">Usuario</label>
                    <input
                        type="text"
                        id="profile-username"
                        class="form-control"
                        value="{{ $username }}"
                        disabled
                    >
                </div>

                <div class="form-group">
                    <label for="profile-status">Estado</label>
                    <input
                        type="text"
                        id="profile-status"
                        class="form-control"
                        value="{{ $status }}"
                        disabled
                    >
                </div>

                <div class="form-group">
                    <label for="profile-areas">Áreas asignadas</label>
                    <input
                        type="text"
                        id="profile-areas"
                        class="form-control"
                        value="{{ $areas }}"
                        disabled
                    >
                </div>

                <div class="form-group mb-0">
                    <label for="profile-roles">Rol</label>
                    <input
                        type="text"
                        id="profile-roles"
                        class="form-control"
                        value="{{ $roles }}"
                        disabled
                    >
                </div>
            </div>
        </div>
    </div>
</div>
