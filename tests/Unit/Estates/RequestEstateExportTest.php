<?php

namespace Tests\Unit\Estates;

use App\Jobs\Estates\GenerateEstatesExport;
use App\Models\User;
use App\Services\Estates\Exports\EstateExportStorage;
use App\Services\Estates\Exports\RequestEstateExport;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class RequestEstateExportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Cache::setDefaultDriver('array');
    }

    public function test_it_stores_pending_status_and_dispatches_the_export_job(): void
    {
        Bus::fake();
        $user = new User;
        $user->id = 15;
        $user->exists = true;
        $filters = $this->filters();

        app(RequestEstateExport::class)->request($user, [2, 7], $filters);

        $status = app(EstateExportStorage::class)->status(15);

        $this->assertSame('pending', $status['status']);
        Bus::assertDispatched(GenerateEstatesExport::class, fn ($job) => $job->userId === 15
            && $job->allowedAreaIds === [2, 7]
            && $job->filters === $filters
            && $job->queue === 'exports');
    }

    public function test_it_rejects_a_second_active_export_for_the_same_user(): void
    {
        Bus::fake();
        $user = new User;
        $user->id = 15;
        $user->exists = true;
        $storage = app(EstateExportStorage::class);
        $storage->putStatus(15, ['status' => 'processing']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Ya tienes una exportación en proceso.');

        app(RequestEstateExport::class)->request($user, [], $this->filters());
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
