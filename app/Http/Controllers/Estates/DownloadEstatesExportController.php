<?php

namespace App\Http\Controllers\Estates;

use App\Http\Controllers\Controller;
use App\Services\Estates\Exports\EstateExportStorage;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadEstatesExportController extends Controller
{
    public function __invoke(Request $request, EstateExportStorage $storage): StreamedResponse
    {
        $userId = (int) $request->user()->getKey();
        $status = $storage->status($userId);

        abort_unless(
            ($status['status'] ?? null) === 'completed' && $storage->completedExists($userId),
            404
        );

        if (now()->greaterThanOrEqualTo($status['expires_at'] ?? now())) {
            $storage->deleteUserExport($userId);
            abort(410, 'La exportación ya venció.');
        }

        return $storage->download($userId, $status['filename'] ?? 'bienes.xlsx');
    }
}
