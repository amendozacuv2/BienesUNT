<?php

namespace App\Services\Employees;

class NormalizeApi
{
    public function employeeData(array $apiResult): array
    {
        return [
            'dni' => $this->cleanDni((string) ($apiResult['id'] ?? '')),
            'name' => $this->cleanName((string) ($apiResult['nombres'] ?? '')),
            'lastname' => $this->cleanName(
                trim(
                    (string) ($apiResult['apellido_paterno'] ?? '') . ' ' .
                    (string) ($apiResult['apellido_materno'] ?? '')
                )
            ),
        ];
    }

    public function cleanDni(?string $dni): string
    {
        return substr(preg_replace('/\D/', '', (string) $dni) ?? '', 0, 8);
    }

    public function cleanName(?string $value): string
    {
        $value = trim((string) $value);
        $value = preg_replace('/\s+/u', ' ', $value) ?? '';

        return mb_strtoupper($value, 'UTF-8');
    }
}
