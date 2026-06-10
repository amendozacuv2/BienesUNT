<?php

namespace App\Console\Commands;

use App\Services\Estates\Exports\EstateExportStorage;
use Illuminate\Console\Command;

class CleanupExpiredEstateExports extends Command
{
    protected $signature = 'estates:cleanup-exports';

    protected $description = 'Elimina archivos temporales de exportación de bienes vencidos';

    public function handle(EstateExportStorage $storage): int
    {
        $deleted = $storage->cleanupExpired();

        $this->info("Archivos temporales eliminados: {$deleted}");

        return self::SUCCESS;
    }
}
