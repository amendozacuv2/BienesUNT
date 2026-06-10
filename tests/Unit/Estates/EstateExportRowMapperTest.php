<?php

namespace Tests\Unit\Estates;

use App\Models\Area;
use App\Models\Employee;
use App\Models\Estate;
use App\Models\Location;
use App\Services\Estates\Exports\EstateExportRowMapper;
use Illuminate\Support\Collection;
use Tests\TestCase;

class EstateExportRowMapperTest extends TestCase
{
    public function test_it_maps_the_fixed_columns_and_active_area_employees(): void
    {
        $area = new Area(['name' => 'Informática']);
        $area->setRelation('employees', new Collection([
            new Employee(['name' => 'Lucía', 'lastname' => 'Salinas']),
            new Employee(['name' => 'Carlos', 'lastname' => 'Mendoza']),
            new Employee(['name' => 'Carlos', 'lastname' => 'Mendoza']),
        ]));

        $location = new Location(['name' => 'Laboratorio 01']);
        $location->setRelation('area', $area);

        $estate = new Estate([
            'patrimonial_code' => '  pat-001 ',
            'internal_code' => 'int-001',
            'denomination' => 'impresora',
            'observation' => "requiere\nmantenimiento",
        ]);
        $estate->setRelation('location', $location);
        $estate->created_at = now()->setDate(2026, 6, 10)->setTime(8, 30);
        $estate->updated_at = now()->setDate(2026, 6, 10)->setTime(9, 45);

        $row = app(EstateExportRowMapper::class)->map($estate);

        $this->assertCount(18, $row);
        $this->assertSame('PAT-001', $row[0]);
        $this->assertSame('INT-001', $row[1]);
        $this->assertSame('IMPRESORA', $row[2]);
        $this->assertSame('REQUIERE MANTENIMIENTO', $row[12]);
        $this->assertSame('LABORATORIO 01', $row[13]);
        $this->assertSame('INFORMÁTICA', $row[14]);
        $this->assertSame('CARLOS MENDOZA; LUCÍA SALINAS', $row[15]);
        $this->assertSame('2026-06-10 08:30:00', $row[16]);
        $this->assertSame('2026-06-10 09:45:00', $row[17]);
    }

    public function test_it_marks_areas_without_an_employee(): void
    {
        $area = new Area(['name' => 'Archivo']);
        $area->setRelation('employees', new Collection);

        $location = new Location(['name' => 'Depósito']);
        $location->setRelation('area', $area);

        $estate = new Estate(['internal_code' => 'INT-002']);
        $estate->setRelation('location', $location);

        $this->assertSame(
            'SIN ENCARGADO',
            app(EstateExportRowMapper::class)->map($estate)[15]
        );
    }
}
