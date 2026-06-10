<?php

namespace App\Livewire\Areas;

use App\Livewire\Concerns\InteractsWithNotifications;
use App\Models\Area;
use App\Services\Areas\SearchAreas;
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

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function destroy(int $areaId): void
    {
        abort_unless(Auth::user()?->can('destroy.area'), 403);

        $area = Area::query()
            ->withCount(['users', 'locations'])
            ->findOrFail($areaId);

        if ($area->users_count > 0 || $area->locations_count > 0) {
            $this->notifyError($this->deleteBlockMessage($area));

            return;
        }

        try {
            $area->delete();

            $this->notifySuccess('Área eliminada correctamente.');
        } catch (Throwable) {
            $this->notifyError('No se pudo eliminar el área. Inténtalo nuevamente.');
        }
    }

    public function render(SearchAreas $searchAreas): View
    {
        $query = Area::query()
            ->withCount(['users', 'locations']);

        $searchAreas->apply($query, $this->search);

        $areas = $query
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.areas.index', [
            'areas' => $areas,
        ])->layout('layouts.app', [
            'title' => 'Áreas',
            'headerTitle' => 'Áreas',
        ]);
    }

    private function deleteBlockMessage(Area $area): string
    {
        $reasons = [];

        if ($area->users_count > 0) {
            $reasons[] = 'usuarios asignados';
        }

        if ($area->locations_count > 0) {
            $reasons[] = 'localidades asignadas';
        }

        return 'No se puede eliminar el área porque tiene ' . implode(' y ', $reasons) . '.';
    }
}
