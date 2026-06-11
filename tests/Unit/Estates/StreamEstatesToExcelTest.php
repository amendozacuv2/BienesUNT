<?php

namespace Tests\Unit\Estates;

use App\Models\Area;
use App\Models\Estate;
use App\Models\Location;
use App\Services\Estates\Exports\EstateExportAreaEmployees;
use App\Services\Estates\Exports\EstateExportQuery;
use App\Services\Estates\Exports\EstateExportRowMapper;
use App\Services\Estates\Exports\StreamEstatesToExcel;
use Illuminate\Database\Query\Builder;
use Mockery;
use OpenSpout\Reader\XLSX\Reader;
use Tests\TestCase;
use ZipArchive;

class StreamEstatesToExcelTest extends TestCase
{
    public function test_it_writes_a_valid_formatted_workbook_and_splits_rows_between_sheets(): void
    {
        config()->set('estates.exports.rows_per_sheet', 1);
        config()->set('estates.exports.chunk_size', 100);

        $query = Mockery::mock(Builder::class);
        $query->shouldReceive('chunkByIdDesc')
            ->once()
            ->withArgs(fn ($chunk, $callback, $column, $alias) => $chunk === 100
                && is_callable($callback)
                && $column === 'estates.id'
                && $alias === 'id')
            ->andReturnUsing(function ($chunk, $callback) {
                $callback(collect([
                    $this->estate('INT-002'),
                    $this->estate('INT-001'),
                ]));

                return true;
            });

        $exportQuery = Mockery::mock(EstateExportQuery::class);
        $exportQuery->shouldReceive('build')->once()->andReturn($query);

        $path = storage_path('framework/testing/stream-estates-export.xlsx');

        $areaEmployees = Mockery::mock(EstateExportAreaEmployees::class);
        $areaEmployees->shouldReceive('warm')->once()->with([1]);
        $areaEmployees->shouldReceive('forArea')->andReturn('SIN ENCARGADO');

        (new StreamEstatesToExcel($exportQuery, new EstateExportRowMapper($areaEmployees), $areaEmployees))
            ->write([1], $this->emptyFilters(), $path);

        $this->assertWorkbookXml($path);
        $this->assertWorkbookRows($path);

        unlink($path);
    }

    private function assertWorkbookXml(string $path): void
    {
        $zip = new ZipArchive;

        $this->assertTrue($zip->open($path));

        $sheet = $zip->getFromName('xl/worksheets/sheet1.xml');

        $this->assertIsString($sheet);
        $this->assertStringContainsString('<autoFilter', $sheet);
        $this->assertStringContainsString('<pane', $sheet);
        $this->assertStringContainsString('<sheetProtection', $sheet);
        $this->assertStringContainsString('<cols>', $sheet);

        $zip->close();
    }

    private function assertWorkbookRows(string $path): void
    {
        $reader = new Reader;
        $reader->open($path);

        $sheets = iterator_to_array($reader->getSheetIterator(), false);

        $this->assertCount(2, $sheets);
        $this->assertSame('Bienes 1', $sheets[0]->getName());
        $this->assertSame('Bienes 2', $sheets[1]->getName());

        foreach ($sheets as $sheet) {
            $this->assertCount(2, iterator_to_array($sheet->getRowIterator()));
        }

        $reader->close();
    }

    private function estate(string $internalCode): Estate
    {
        $area = new Area(['name' => 'INFORMÁTICA']);
        $area->setRelation('employees', collect());

        $location = new Location(['name' => 'LABORATORIO']);
        $location->setRelation('area', $area);

        $estate = new Estate(['internal_code' => $internalCode]);
        $estate->setRelation('location', $location);

        return $estate;
    }

    private function emptyFilters(): array
    {
        return [
            'search' => null,
            'areaId' => null,
            'locationId' => null,
            'situation' => null,
            'conservationStatus' => null,
        ];
    }
}
