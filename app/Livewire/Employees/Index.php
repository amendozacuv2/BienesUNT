<?php

namespace App\Livewire\Employees;

use App\Livewire\Concerns\InteractsWithNotifications;
use App\Models\Area;
use App\Models\Employee;
use App\Services\Employees\SearchEmployees;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    public function destroy(int $employeeId): void
    {
        abort_unless(Auth::user()?->can('destroy.employee'), 403);

        $allowedAreaIds = $this->allowedAreaIds();

        if (empty($allowedAreaIds)) {
            $this->notifyError('No tienes áreas asignadas para realizar esta acción.');

            return;
        }

        $employee = Employee::query()
            ->whereHas('areas', function ($query) use ($allowedAreaIds) {
                $query->whereIn('areas.id', $allowedAreaIds)
                    ->where('employee_area.is_active', true);
            })
            ->with('areas.locations.estates')
            ->findOrFail($employeeId);

        if ($this->hasAreasWithEstates($employee)) {
            $this->notifyError(
                'No se puede eliminar este empleado porque está asignado a un área que tiene bienes registrados.'
            );

            return;
        }

        try {
            DB::transaction(function () use ($employee) {
                $employee->areas()->detach();
                $employee->delete();
            });

            $this->notifySuccess('Empleado eliminado correctamente.');
        } catch (Throwable) {
            $this->notifyError('No se pudo eliminar el empleado. Inténtalo nuevamente.');
        }
    }

    public function render(SearchEmployees $searchEmployees): View
    {
        $allowedAreaIds = $this->allowedAreaIds();

        if (filled($this->areaId) && ! in_array((int) $this->areaId, $allowedAreaIds, true)) {
            $this->areaId = '';
            $this->resetPage();
        }

        $query = Employee::query()
            ->with(['areas' => function ($query) use ($allowedAreaIds) {
                $query->whereIn('areas.id', $allowedAreaIds)
                    ->where('employee_area.is_active', true)
                    ->orderBy('areas.name');
            }]);

        $searchEmployees->apply($query, $this->search, $this->areaId, $allowedAreaIds);

        $employees = $query
            ->orderBy('lastname')
            ->orderBy('name')
            ->paginate(10);

        $areas = $this->allowedAreas();

        return view('livewire.employees.index', [
            'employees' => $employees,
            'areas' => $areas,
        ])->layout('layouts.app', [
            'title' => 'Empleados',
            'headerTitle' => 'Empleados',
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

    private function hasAreasWithEstates(Employee $employee): bool
    {
        return $employee->areas->contains(function (Area $area) {
            return $area->locations->contains(function ($location) {
                return $location->estates->isNotEmpty();
            });
        });
    }
}
