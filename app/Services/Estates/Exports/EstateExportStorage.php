<?php

namespace App\Services\Estates\Exports;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class EstateExportStorage
{
    public function status(int $userId): ?array
    {
        $contents = $this->disk()->get($this->statusPath($userId));

        if (! is_string($contents)) {
            return null;
        }

        $status = json_decode($contents, true);

        return is_array($status) ? $status : null;
    }

    public function putStatus(int $userId, array $status): void
    {
        $this->disk()->put(
            $this->statusPath($userId),
            json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)
        );
    }

    public function isActive(int $userId): bool
    {
        return in_array($this->status($userId)['status'] ?? null, ['pending', 'processing'], true);
    }

    public function prepare(int $userId): void
    {
        $this->disk()->delete([
            $this->completedPath($userId),
            $this->partialPath($userId),
            $this->statusPath($userId),
        ]);

        $this->disk()->makeDirectory($this->directory($userId));
    }

    public function partialAbsolutePath(int $userId): string
    {
        $this->disk()->makeDirectory($this->directory($userId));

        return $this->disk()->path($this->partialPath($userId));
    }

    public function complete(int $userId): void
    {
        $this->disk()->delete($this->completedPath($userId));

        if (! $this->disk()->move($this->partialPath($userId), $this->completedPath($userId))) {
            throw new \RuntimeException('No se pudo finalizar el archivo de exportación.');
        }
    }

    public function deletePartial(int $userId): void
    {
        $this->disk()->delete($this->partialPath($userId));
    }

    public function completedPath(int $userId): string
    {
        return $this->directory($userId).'/latest.xlsx';
    }

    public function completedExists(int $userId): bool
    {
        return $this->disk()->exists($this->completedPath($userId));
    }

    public function completedSize(int $userId): int
    {
        return $this->disk()->size($this->completedPath($userId));
    }

    public function download(int $userId, string $filename): StreamedResponse
    {
        return $this->disk()->download(
            $this->completedPath($userId),
            $filename,
            ['Cache-Control' => 'no-store, private']
        );
    }

    public function deleteUserExport(int $userId): void
    {
        $this->disk()->deleteDirectory($this->directory($userId));
    }

    public function cleanupExpired(): int
    {
        $deleted = 0;
        $cutoff = now()->subHours($this->expiresHours())->getTimestamp();

        foreach ($this->disk()->allFiles($this->baseDirectory()) as $path) {
            try {
                if ($this->disk()->lastModified($path) >= $cutoff) {
                    continue;
                }

                if ($this->disk()->delete($path)) {
                    $deleted++;
                }
            } catch (Throwable) {
                // El worker puede mover un archivo parcial mientras se ejecuta la limpieza.
            }
        }

        return $deleted;
    }

    public function diskName(): string
    {
        return (string) config('estates.exports.disk', 'local');
    }

    public function expiresHours(): int
    {
        return max(1, (int) config('estates.exports.expires_hours', 24));
    }

    private function partialPath(int $userId): string
    {
        return $this->directory($userId).'/latest.part';
    }

    private function statusPath(int $userId): string
    {
        return $this->directory($userId).'/status.json';
    }

    private function directory(int $userId): string
    {
        return $this->baseDirectory().'/'.$userId;
    }

    private function baseDirectory(): string
    {
        return trim((string) config('estates.exports.directory', 'exports/estates'), '/');
    }

    private function disk(): Filesystem
    {
        return Storage::disk($this->diskName());
    }
}
