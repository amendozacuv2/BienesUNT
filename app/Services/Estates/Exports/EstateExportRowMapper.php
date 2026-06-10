<?php

namespace App\Services\Estates\Exports;

use App\Models\Estate;

class EstateExportRowMapper
{
    public function map(Estate $estate): array
    {
        return [
            $this->text($estate->patrimonial_code),
            $this->text($estate->internal_code),
            $this->text($estate->denomination),
            $this->text($estate->brand),
            $this->text($estate->model),
            $this->text($estate->type),
            $this->text($estate->color),
            $this->text($estate->series),
            $this->text($estate->dimensions),
            $this->text($estate->others),
            $this->text($estate->situation),
            $this->text($estate->conservation_status),
            $this->text($estate->observation),
            $this->text($estate->location?->name),
            $this->text($estate->location?->area?->name),
            $this->areaEmployees($estate),
            $estate->created_at?->format('Y-m-d H:i:s') ?? '',
            $estate->updated_at?->format('Y-m-d H:i:s') ?? '',
        ];
    }

    private function areaEmployees(Estate $estate): string
    {
        $employees = $estate->location?->area?->employees;

        if (! $employees || $employees->isEmpty()) {
            return 'SIN ENCARGADO';
        }

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
