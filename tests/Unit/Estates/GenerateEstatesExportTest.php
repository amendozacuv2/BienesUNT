<?php

namespace Tests\Unit\Estates;

use App\Jobs\Estates\GenerateEstatesExport;
use App\Services\Estates\Exports\EstateExportStorage;
use App\Services\Estates\Exports\StreamEstatesToExcel;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class GenerateEstatesExportTest extends TestCase
{
    public function test_job_generates_and_marks_the_private_export_as_completed(): void
    {
        Storage::fake('local');
        $storage = app(EstateExportStorage::class);
        $storage->putStatus(9, [
            'status' => 'pending',
            'filename' => 'bienes_prueba.xlsx',
            'requested_at' => now()->toIso8601String(),
        ]);

        $exporter = Mockery::mock(StreamEstatesToExcel::class);
        $exporter->shouldReceive('write')
            ->once()
            ->with([3], $this->filters(), Mockery::type('string'))
            ->andReturnUsing(function ($areas, $filters, $path) {
                file_put_contents($path, 'xlsx-content');
            });

        (new GenerateEstatesExport(9, [3], $this->filters()))->handle($exporter, $storage);

        $status = $storage->status(9);

        $this->assertSame('completed', $status['status']);
        $this->assertNotEmpty($status['expires_at']);
        $this->assertSame(strlen('xlsx-content'), $status['size']);
        Storage::disk('local')->assertExists($storage->completedPath(9));
    }

    private function filters(): array
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
