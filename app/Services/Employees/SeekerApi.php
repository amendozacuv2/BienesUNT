<?php

namespace App\Services\Employees;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Throwable;

class SeekerApi
{
    private const ENDPOINT = 'https://api.perudevs.com/api/v1/dni/simple';

    public function search(string $document): array
    {
        $document = $this->cleanDocument($document);

        if (! preg_match('/^\d{8}$/', $document)) {
            return $this->notFound('Ingresa un DNI válido de 8 dígitos.');
        }

        $apiKey = config('services.perudevs.dni_key');

        if (blank($apiKey)) {
            return $this->unavailable();
        }

        try {
            $response = Http::timeout(8)
                ->retry(2, 250)
                ->acceptJson()
                ->get(self::ENDPOINT, [
                    'document' => $document,
                    'key' => $apiKey,
                ]);

            if (! $response->ok()) {
                return $this->unavailable();
            }

            $payload = $response->json();

            if (
                ! is_array($payload) ||
                ($payload['estado'] ?? false) !== true ||
                blank($payload['resultado'] ?? null)
            ) {
                return $this->notFound(
                    'No encontramos información para este DNI. Puedes completar los datos manualmente.'
                );
            }

            return [
                'ok' => true,
                'found' => true,
                'message' => 'DNI encontrado. Completamos los datos automáticamente.',
                'data' => $payload['resultado'],
            ];
        } catch (ConnectionException|Throwable) {
            return $this->unavailable();
        }
    }

    private function cleanDocument(string $document): string
    {
        return substr(preg_replace('/\D/', '', $document) ?? '', 0, 8);
    }

    private function notFound(string $message): array
    {
        return [
            'ok' => true,
            'found' => false,
            'message' => $message,
            'data' => [],
        ];
    }

    private function unavailable(): array
    {
        return [
            'ok' => false,
            'found' => false,
            'message' => 'No pudimos consultar el DNI en este momento. Puedes completar los datos manualmente.',
            'data' => [],
        ];
    }
}
