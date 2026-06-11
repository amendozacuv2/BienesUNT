<?php

namespace App\Services\Estates\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EstateExportAreaEmployees
{
    private array $employeesByArea = [];

    public function warm(array $areaIds): void
    {
        $areaIds = collect($areaIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($areaIds->isEmpty()) {
            $this->employeesByArea = [];

            return;
        }

        $employees = DB::table('employee_area')
            ->join('employees', 'employees.id', '=', 'employee_area.employee_id')
            ->whereIn('employee_area.area_id', $areaIds)
            ->where('employee_area.is_active', true)
            ->where('employees.is_active', true)
            ->select([
                'employee_area.area_id',
                'employees.name',
                'employees.lastname',
            ])
            ->orderBy('employees.lastname')
            ->orderBy('employees.name')
            ->get()
            ->groupBy('area_id');

        $this->employeesByArea = $employees
            ->map(fn (Collection $employees) => $this->formatEmployees($employees))
            ->all();
    }

    public function forArea(?int $areaId): string
    {
        if (! $areaId) {
            return 'SIN ENCARGADO';
        }

        return $this->employeesByArea[$areaId] ?? 'SIN ENCARGADO';
    }

    private function formatEmployees(Collection $employees): string
    {
        $names = $employees
            ->map(fn ($employee) => $this->text(trim($employee->name.' '.$employee->lastname)))
            ->filter()
            ->unique()
            ->sort(SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        return $names->isEmpty() ? 'SIN ENCARGADO' : $names->implode('; ');
    }

    private function text(mixed $value): string
    {
        $value = preg_replace('/\s+/u', ' ', trim((string) ($value ?? '')));

        return mb_strtoupper($value ?? '');
    }
}
