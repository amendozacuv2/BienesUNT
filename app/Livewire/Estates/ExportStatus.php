<?php

namespace App\Livewire\Estates;

use App\Services\Estates\Exports\EstateExportStorage;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class ExportStatus extends Component
{
    #[On('estate-export-requested')]
    public function refreshStatus(): void
    {
        //
    }

    public function render(EstateExportStorage $storage): View
    {
        return view('livewire.estates.export-status', [
            'exportStatus' => Auth::id() ? $storage->status((int) Auth::id()) : null,
        ]);
    }
}
