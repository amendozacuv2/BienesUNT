<?php

namespace App\Jobs\Estates;

use App\Services\Estates\Exports\EstateExportStorage;
use App\Services\Estates\Exports\StreamEstatesToExcel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class GenerateEstatesExport implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public int $timeout = 3600;

    public bool $failOnTimeout = true;

    public function __construct(
        public readonly int $userId,
        public readonly array $allowedAreaIds,
        public readonly array $filters
    ) {}

    public function handle(StreamEstatesToExcel $exporter, EstateExportStorage $storage): void
    {
        $status = $storage->status($this->userId) ?? [];

        $storage->putStatus($this->userId, array_merge($status, [
            'status' => 'processing',
            'started_at' => now()->toIso8601String(),
            'message' => 'El archivo Excel se está generando.',
        ]));

        $storage->deletePartial($this->userId);

        try {
            $exporter->write(
                allowedAreaIds: $this->allowedAreaIds,
                filters: $this->filters,
                outputPath: $storage->partialAbsolutePath($this->userId)
            );

            $storage->complete($this->userId);

            $storage->putStatus($this->userId, array_merge($status, [
                'status' => 'completed',
                'started_at' => $storage->status($this->userId)['started_at'] ?? now()->toIso8601String(),
                'completed_at' => now()->toIso8601String(),
                'expires_at' => now()->addHours($storage->expiresHours())->toIso8601String(),
                'size' => $storage->completedSize($this->userId),
                'message' => 'La exportación está lista para descargar.',
            ]));
        } catch (Throwable $exception) {
            $storage->deletePartial($this->userId);

            throw $exception;
        }
    }

    public function failed(?Throwable $exception): void
    {
        $storage = app(EstateExportStorage::class);
        $storage->deletePartial($this->userId);

        $storage->putStatus($this->userId, array_merge($storage->status($this->userId) ?? [], [
            'status' => 'failed',
            'completed_at' => now()->toIso8601String(),
            'message' => 'No se pudo generar la exportación. Inténtalo nuevamente.',
        ]));
    }
}
