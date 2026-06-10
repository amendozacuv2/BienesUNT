<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AreaLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Definimos la estructura de datos requerida
        $areasData = [
            'INFORMATICA' => [
                'LABORATORIO 01',
                'LABORATORIO 02',
                'LABORATORIO 03'
            ],
            'GERENCIA MUNICIPAL' => [
                'PALACIO MUNICIPAL'
            ],
            'OFICINA DE ARCHIVO CENTRAL' => [
                'PALACIO MUNICIPAL (TERCER PISO)',
                'INMUEBLE ALQUILADO',
                'PALACIO MUNICIPAL (PRIMER PISO)'
            ],
        ];

        $now = Carbon::now();

        foreach ($areasData as $areaName => $locations) {
            // 1. Insertar el Área y obtener su ID
            $areaId = DB::table('areas')->insertGetId([
                'uuid'       => Str::uuid()->toString(),
                'name'       => $areaName,
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // 2. Preparar el array de ubicaciones para esa área
            $locationsToInsert = [];
            foreach ($locations as $locationName) {
                $locationsToInsert[] = [
                    'uuid'       => Str::uuid()->toString(),
                    'name'       => $locationName,
                    'area_id'    => $areaId,
                    'is_active'  => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // 3. Insertar las ubicaciones en bloque
            DB::table('locations')->insert($locationsToInsert);
        }
    }
}