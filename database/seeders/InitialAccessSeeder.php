<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use App\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class InitialAccessSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $role = Role::firstOrCreate([
            'name' => 'Administrador',
            'guard_name' => 'web',
        ]);

        $permissionsData = [
            [
                'name' => 'view.profile',
                'guard_name' => 'web',
                'order' => 1,
                'description' => 'Ver Perfil',
            ],
            // ROLES
            [
                'name' => 'view.role',
                'guard_name' => 'web',
                'order' => 10,
                'description' => 'Ver Rol',
            ],
            [
                'name' => 'edit.role',
                'guard_name' => 'web',
                'order' => 11,
                'description' => 'Editar Rol',
            ],
            [
                'name' => 'create.role',
                'guard_name' => 'web',
                'order' => 12,
                'description' => 'Crear Rol',
            ],
            [
                'name' => 'destroy.role',
                'guard_name' => 'web',
                'order' => 13,
                'description' => 'Eliminar Rol',
            ],
            // ÁREAS DE TRABAJO
            [
                'name' => 'view.area',
                'guard_name' => 'web',
                'order' => 20,
                'description' => 'Ver Área',
            ],
            [
                'name' => 'edit.area',
                'guard_name' => 'web',
                'order' => 21,
                'description' => 'Editar Área',
            ],
            [
                'name' => 'create.area',
                'guard_name' => 'web',
                'order' => 22,
                'description' => 'Crear Área',
            ],
            [
                'name' => 'destroy.area',
                'guard_name' => 'web',
                'order' => 23,
                'description' => 'Eliminar Área',
            ],
            // UBICACIONES
            [
                'name' => 'view.location',
                'guard_name' => 'web',
                'order' => 30,
                'description' => 'Ver Ubicación',
            ],
            [
                'name' => 'edit.location',
                'guard_name' => 'web',
                'order' => 31,
                'description' => 'Editar Ubicación',
            ],
            [
                'name' => 'create.location',
                'guard_name' => 'web',
                'order' => 32,
                'description' => 'Crear Ubicación',
            ],
            [
                'name' => 'destroy.location',
                'guard_name' => 'web',
                'order' => 33,
                'description' => 'Eliminar Ubicación',
            ],
            // EMPLEADOS
            [
                'name' => 'view.employee',
                'guard_name' => 'web',
                'order' => 40,
                'description' => 'Ver Empleado',
            ],
            [
                'name' => 'edit.employee',
                'guard_name' => 'web',
                'order' => 41,
                'description' => 'Editar Empleado',
            ],
            [
                'name' => 'create.employee',
                'guard_name' => 'web',
                'order' => 42,
                'description' => 'Crear Empleado',
            ],
            [
                'name' => 'destroy.employee',
                'guard_name' => 'web',
                'order' => 43,
                'description' => 'Eliminar Empleado',
            ],
            // USUARIOS
            [
                'name' => 'view.user',
                'guard_name' => 'web',
                'order' => 50,
                'description' => 'Ver Usuario',
            ],
            [
                'name' => 'edit.user',
                'guard_name' => 'web',
                'order' => 51,
                'description' => 'Editar Usuario',
            ],
            [
                'name' => 'create.user',
                'guard_name' => 'web',
                'order' => 52,
                'description' => 'Crear Usuario',
            ],
            [
                'name' => 'destroy.user',
                'guard_name' => 'web',
                'order' => 53,
                'description' => 'Eliminar Usuario',
            ],
            // BIENES
            [
                'name' => 'view.estate',
                'guard_name' => 'web',
                'order' => 60,
                'description' => 'Ver Bien',
            ],
            [
                'name' => 'edit.estate',
                'guard_name' => 'web',
                'order' => 61,
                'description' => 'Editar Bien',
            ],
            [
                'name' => 'create.estate',
                'guard_name' => 'web',
                'order' => 62,
                'description' => 'Crear Bien',
            ],
            [
                'name' => 'destroy.estate',
                'guard_name' => 'web',
                'order' => 63,
                'description' => 'Eliminar Bien',
            ],

        ];

        $permissions = collect($permissionsData)->map(function (array $permissionData) {
            $permission = Permission::firstOrCreate(
                [
                    'name' => $permissionData['name'],
                    'guard_name' => $permissionData['guard_name'],
                ],
                [
                    'order' => $permissionData['order'],
                    'description' => $permissionData['description'],
                ]
            );

            $permission->update([
                'order' => $permissionData['order'],
                'description' => $permissionData['description'],
            ]);

            return $permission;
        });

        $role->givePermissionTo($permissions);

        $user = User::updateOrCreate(
            [
                'username' => 'amendoza',
            ],
            [
                'name' => 'ANGEL MENDOZA',
                'password' => Hash::make('Amigo123'),
                'is_active' => true,
            ]
        );

        if (! $user->hasRole('Administrador')) {
            $user->assignRole('Administrador');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
