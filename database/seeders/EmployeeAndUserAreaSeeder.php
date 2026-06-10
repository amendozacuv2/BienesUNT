<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EmployeeAndUserAreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // 1. Obtener dos áreas creadas previamente
        $area1 = DB::table('areas')->where('name', 'INFORMATICA')->first();
        $area2 = DB::table('areas')->where('name', 'GERENCIA MUNICIPAL')->first();

        // Verificamos que existan para evitar errores
        if (!$area1 || !$area2) {
            $this->command->warn('Asegúrate de haber ejecutado AreaLocationSeeder primero para tener las áreas disponibles.');
            return;
        }

        // 2. Crear Empleado 1
        $employee1Id = DB::table('employees')->insertGetId([
            'uuid'       => Str::uuid()->toString(),
            'dni'        => '72345678', // 8 caracteres, único
            'name'       => 'Carlos Alberto',
            'lastname'   => 'Mendoza Ruiz',
            'is_active'  => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // 3. Crear Empleado 2
        $employee2Id = DB::table('employees')->insertGetId([
            'uuid'       => Str::uuid()->toString(),
            'dni'        => '45678912', // 8 caracteres, único
            'name'       => 'Lucía Fernanda',
            'lastname'   => 'Salinas Pérez',
            'is_active'  => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // 4. Asignar Empleados a sus respectivas Áreas (employee_area)
        DB::table('employee_area')->insert([
            [
                'employee_id' => $employee1Id,
                'area_id'     => $area1->id,
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'employee_id' => $employee2Id,
                'area_id'     => $area2->id,
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]
        ]);

        // 5. Asignar las áreas al Usuario Admin (id = 1) en user_area
        $adminUserId = 1;
        
        // Usamos insertOrIgnore por si ya se corrió el seeder antes y no duplicar registros (violación de unique)
        DB::table('user_area')->insertOrIgnore([
            [
                'user_id'    => $adminUserId,
                'area_id'    => $area1->id,
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id'    => $adminUserId,
                'area_id'    => $area2->id,
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        ]);
    }
}