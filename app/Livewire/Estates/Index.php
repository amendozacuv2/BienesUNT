<?php

namespace App\Livewire\Estates;

use App\Livewire\Concerns\InteractsWithNotifications;
use App\Models\Area;
use App\Models\Estate;
use App\Models\Location;
use App\Services\Estates\EstateAuditLogger;
use App\Services\Estates\EstateFieldSuggestions;
use App\Services\Estates\SearchEstates;
use App\Services\Estates\WidgetsService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

class Index extends Component
{
    use InteractsWithNotifications;
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $search = '';

    public string $areaId = '';

    public string $locationId = '';

    public string $situation = '';

    public string $conservationStatus = '';

    public string $perPage = '10';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedAreaId(): void
    {
        $this->locationId = '';
        $this->resetPage();
    }

    public function updatedLocationId(): void
    {
        $this->resetPage();
    }

    public function updatedSituation(): void
    {
        $this->resetPage();
    }

    public function updatedConservationStatus(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        if (! in_array($this->perPage, ['10', '25', '50'], true)) {
            $this->perPage = '10';
        }

        $this->resetPage();
    }

    public function destroy(string $uuid): void
    {
        abort_unless(Auth::user()?->can('destroy.estate'), 403);

        try {
            DB::transaction(function () use ($uuid) {
                $estate = Estate::query()
                    ->with(['location.area'])
                    ->where('uuid', $uuid)
                    ->whereHas('location', function ($query) {
                        $query->whereIn('area_id', $this->assignedAreaIds());
                    })
                    ->firstOrFail();

                app(EstateAuditLogger::class)->deleted($estate);

                $estate->delete();
            });

            app(EstateFieldSuggestions::class)->invalidateCache();

            $this->notifySuccess('El bien fue eliminado correctamente.');
        } catch (Throwable) {
            $this->notifyError('No se pudo eliminar el bien seleccionado.');
        }
    }

    public function render(): View
    {
        abort_unless(Auth::user()?->can('view.estate'), 403);

        $allowedAreaIds = $this->assignedAreaIds();

        $areaId = $this->areaId !== '' ? (int) $this->areaId : null;
        $locationId = $this->locationId !== '' ? (int) $this->locationId : null;
        $situation = $this->situation !== '' ? $this->situation : null;
        $conservationStatus = $this->conservationStatus !== '' ? $this->conservationStatus : null;

        $areas = Area::query()
            ->whereIn('id', $allowedAreaIds)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $locations = Location::query()
            ->whereIn('area_id', $allowedAreaIds)
            ->when($this->areaId !== '', function ($query) {
                $query->where('area_id', (int) $this->areaId);
            }, function ($query) {
                $query->whereRaw('1 = 0');
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $estatesQuery = app(SearchEstates::class)->forIndex(
            $allowedAreaIds,
            $this->search,
            $areaId,
            $locationId,
            $situation,
            $conservationStatus
        );

        $estates = $estatesQuery
            ->latest('id')
            ->paginate((int) $this->perPage);

        $widgets = app(WidgetsService::class)->forIndex(
            $allowedAreaIds,
            $this->search,
            $areaId,
            $locationId,
            $situation,
            $conservationStatus
        );

        return view('livewire.estates.index', [
            'areas' => $areas,
            'locations' => $locations,
            'estates' => $estates,
            'widgets' => $widgets,
            'situations' => Estate::SITUATIONS,
            'conservationStatuses' => Estate::CONSERVATION_STATUSES,
        ])->layout('layouts.app', [
            'title' => 'Bienes',
            'headerTitle' => 'Bienes',
        ]);
    }

    private function assignedAreaIds(): array
    {
        return Auth::user()
            ?->activeAreas()
            ->pluck('areas.id')
            ->map(fn ($id) => (int) $id)
            ->all() ?? [];
    }
}
