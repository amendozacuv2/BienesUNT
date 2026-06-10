<?php

namespace App\Services\Estates\Exports;

use App\Jobs\Estates\GenerateEstatesExport;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

class RequestEstateExport
{
    public function __construct(
        private readonly EstateExportStorage $storage
    ) {}

    public function request(User $user, array $allowedAreaIds, array $filters): void
    {
        Cache::lock('estates-export-request:'.$user->getKey(), 10)->block(3, function () use (
            $user,
            $allowedAreaIds,
            $filters
        ) {
            if ($this->storage->isActive((int) $user->getKey())) {
                throw new RuntimeException('Ya tienes una exportación en proceso.');
            }

            $this->storage->prepare((int) $user->getKey());
            $this->storage->putStatus((int) $user->getKey(), [
                'status' => 'pending',
                'filename' => 'bienes_'.now()->format('Ymd_His').'.xlsx',
                'requested_at' => now()->toIso8601String(),
                'started_at' => null,
                'completed_at' => null,
                'expires_at' => null,
                'size' => null,
                'message' => 'La exportación está esperando un worker disponible.',
            ]);

            GenerateEstatesExport::dispatch(
                userId: (int) $user->getKey(),
                allowedAreaIds: $allowedAreaIds,
                filters: $filters
            )->onQueue((string) config('estates.exports.queue', 'exports'));
        });
    }
}
