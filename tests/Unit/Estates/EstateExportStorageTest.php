<?php

namespace Tests\Unit\Estates;

use App\Services\Estates\Exports\EstateExportStorage;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EstateExportStorageTest extends TestCase
{
    public function test_cleanup_deletes_only_files_older_than_the_retention_period(): void
    {
        Storage::fake('local');
        config()->set('estates.exports.expires_hours', 24);
        $storage = app(EstateExportStorage::class);

        Storage::disk('local')->put('exports/estates/1/old.xlsx', 'old');
        Storage::disk('local')->put('exports/estates/2/recent.xlsx', 'recent');
        touch(Storage::disk('local')->path('exports/estates/1/old.xlsx'), now()->subHours(25)->timestamp);

        $this->assertSame(1, $storage->cleanupExpired());

        Storage::disk('local')->assertMissing('exports/estates/1/old.xlsx');
        Storage::disk('local')->assertExists('exports/estates/2/recent.xlsx');
    }
}
