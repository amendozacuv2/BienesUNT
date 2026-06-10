<?php

namespace Tests\Feature\Estates;

use App\Models\User;
use App\Services\Estates\Exports\EstateExportStorage;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class DownloadEstatesExportTest extends TestCase
{
    public function test_download_requires_authentication(): void
    {
        $this->get(route('estates.export.download'))->assertRedirect(route('login'));
    }

    public function test_download_requires_export_permission(): void
    {
        $this->actingAs($this->userWithExportPermission(false))
            ->get(route('estates.export.download'))
            ->assertForbidden();
    }

    public function test_user_can_download_own_completed_export(): void
    {
        Storage::fake('local');
        $user = $this->userWithExportPermission();
        $storage = app(EstateExportStorage::class);

        Storage::disk('local')->put($storage->completedPath(1), 'xlsx-content');
        $storage->putStatus(1, [
            'status' => 'completed',
            'filename' => 'bienes_prueba.xlsx',
            'expires_at' => now()->addDay()->toIso8601String(),
        ]);

        $this->actingAs($user)
            ->get(route('estates.export.download'))
            ->assertOk()
            ->assertDownload('bienes_prueba.xlsx');
    }

    public function test_expired_export_is_deleted(): void
    {
        Storage::fake('local');
        $user = $this->userWithExportPermission();
        $storage = app(EstateExportStorage::class);

        Storage::disk('local')->put($storage->completedPath(1), 'xlsx-content');
        $storage->putStatus(1, [
            'status' => 'completed',
            'filename' => 'bienes_prueba.xlsx',
            'expires_at' => now()->subMinute()->toIso8601String(),
        ]);

        $this->actingAs($user)
            ->get(route('estates.export.download'))
            ->assertGone();

        Storage::disk('local')->assertMissing($storage->completedPath(1));
    }

    private function userWithExportPermission(bool $allowed = true): User
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->id = 1;
        $user->exists = true;
        $user->shouldReceive('getAuthIdentifier')->andReturn(1);
        $user->shouldReceive('can')->with('export.estate')->andReturn($allowed);
        $user->shouldReceive('canAny')->with(['export.estate'])->andReturn($allowed);

        return $user;
    }
}
