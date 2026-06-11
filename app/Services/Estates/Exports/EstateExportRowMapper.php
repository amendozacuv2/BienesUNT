<?php

namespace App\Services\Estates\Exports;

class EstateExportRowMapper
{
    private EstateExportAreaEmployees $areaEmployees;

    public function __construct(?EstateExportAreaEmployees $areaEmployees = null)
    {
        $this->areaEmployees = $areaEmployees ?? app(EstateExportAreaEmployees::class);
    }

    public function map(object $estate): array
    {
        return [
            $this->text($estate->patrimonial_code ?? null),
            $this->text($estate->internal_code ?? null),
            $this->text($estate->denomination ?? null),
            $this->text($estate->brand ?? null),
            $this->text($estate->model ?? null),
            $this->text($estate->type ?? null),
            $this->text($estate->color ?? null),
            $this->text($estate->series ?? null),
            $this->text($estate->dimensions ?? null),
            $this->text($estate->others ?? null),
            $this->text($estate->situation ?? null),
            $this->text($estate->conservation_status ?? null),
            $this->text($estate->observation ?? null),
            $this->text($this->locationName($estate)),
            $this->text($this->areaName($estate)),
            $this->areaEmployees($estate),
            $this->date($estate->created_at ?? null),
            $this->date($estate->updated_at ?? null),
        ];
    }

    private function locationName(object $estate): mixed
    {
        return $estate->location_name ?? $estate->location?->name ?? null;
    }

    private function areaName(object $estate): mixed
    {
        return $estate->area_name ?? $estate->location?->area?->name ?? null;
    }

    private function areaEmployees(object $estate): string
    {
        if (isset($estate->area_id)) {
            return $this->areaEmployees->forArea((int) $estate->area_id);
        }

        $employees = $estate->location?->area?->employees ?? null;

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

    private function date(mixed $value): string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        return (string) ($value ?? '');
    }

    private function text(mixed $value): string
    {
        $value = preg_replace('/\s+/u', ' ', trim((string) ($value ?? '')));

        return mb_strtoupper($value ?? '');
    }
}
