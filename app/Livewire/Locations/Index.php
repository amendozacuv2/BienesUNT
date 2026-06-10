<?php

namespace App\Livewire\Locations;

use App\Livewire\Concerns\InteractsWithNotifications;
use App\Models\Area;
use App\Models\Location;
use App\Services\Locations\SearchLocations;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;
use Throwable;

class Index extends Component
{
    use InteractsWithNotifications;
    use WithPagination;
    use WithoutUrlPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $search = '';

    public string $areaId = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingAreaId(): void
    {
        $this->resetPage();
    }

    public function destroy(int $locationId): void
    {
        abort_unless(Auth::user()?->can('destroy.location'), 403);

        $allowedAreaIds = $this->allowedAreaIds();

        if (empty($allowedAreaIds)) {
            $this->notifyError('No tienes áreas asignadas para realizar esta acción.');

            return;
        }

        $location = Location::query()
            ->whereIn('locations.area_id', $allowedAreaIds)
            ->withCount('estates')
            ->find($locationId);

        if (! $location) {
            $this->notifyError('No puedes eliminar esta ubicación.');

            return;
        }

        if ($location->estates_count > 0) {
            $this->notifyError('No se puede eliminar la ubicación porque tiene bienes registrados.');

            return;
        }

        try {
            $location->delete();

            $this->notifySuccess('Ubicación eliminada correctamente.');
        } catch (Throwable) {
            $this->notifyError('No se pudo eliminar la ubicación. Inténtalo nuevamente.');
        }
    }

    public function render(SearchLocations $searchLocations): View
    {
        $allowedAreaIds = $this->allowedAreaIds();

        if (filled($this->areaId) && ! in_array((int) $this->areaId, $allowedAreaIds, true)) {
            $this->areaId = '';
            $this->resetPage();
        }

        $query = Location::query()
            ->with('area');

        $searchLocations->apply($query, $this->search, $this->areaId, $allowedAreaIds);

        $locations = $query
            ->join('areas', 'areas.id', '=', 'locations.area_id')
            ->select('locations.*')
            ->orderBy('areas.name')
            ->orderBy('locations.name')
            ->paginate(10);

        $areas = $this->allowedAreas();

        return view('livewire.locations.index', [
            'locations' => $locations,
            'areas' => $areas,
        ])->layout('layouts.app', [
            'title' => 'Ubicaciones',
            'headerTitle' => 'Ubicaciones',
        ]);
    }

    private function allowedAreas()
    {
        return Area::query()
            ->whereIn('id', $this->allowedAreaIds())
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function allowedAreaIds(): array
    {
        return Auth::user()
            ?->activeAreas()
            ->pluck('areas.id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->toArray() ?? [];
    }
}
