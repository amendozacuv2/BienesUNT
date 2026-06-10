<?php

namespace App\Services\Estates;

use App\Models\Estate;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SaveEstateBatch
{
    public function __construct(
        private readonly EstateLocationAccessService $locationAccess,
        private readonly NormalizeEstateData $normalizer,
        private readonly EstateAuditLogger $logger,
        private readonly EstateFieldSuggestions $fieldSuggestions
    ) {}

    public function handle(User $user, int $areaId, int $locationId, array $rows): array
    {
        $hasNewRows = collect($rows)->contains(fn ($row) => empty($row['existing_uuid']));
        $hasExistingRows = collect($rows)->contains(fn ($row) => ! empty($row['existing_uuid']));

        if ($hasNewRows && ! $user->can('create.estate')) {
            throw new AuthorizationException;
        }

        if ($hasExistingRows && ! $user->can('edit.estate')) {
            throw new AuthorizationException;
        }

        $location = $this->locationAccess->findAllowedLocation($user, $areaId, $locationId);

        if (! $location) {
            throw new InvalidArgumentException('Selecciona una ubicación válida para registrar los bienes.');
        }

        $result = DB::transaction(function () use ($rows, $location) {
            $created = 0;
            $updated = 0;

            foreach (array_values($rows) as $row) {
                $data = $this->normalizer->fromRow($row, (int) $location->id);

                if (! empty($row['existing_uuid'])) {
                    $estate = Estate::query()
                        ->with(['location.area'])
                        ->where('uuid', $row['existing_uuid'])
                        ->firstOrFail();

                    $oldValues = $this->logger->estateValues($estate);
                    $oldLocationId = (int) $estate->location_id;

                    $estate->update($data);
                    $estate->refresh()->load(['location.area']);

                    $newValues = $this->logger->estateValues($estate);

                    $this->logger->updated($estate, $oldValues, $newValues);

                    if ($oldLocationId !== (int) $estate->location_id) {
                        $this->logger->changedLocation($estate, $oldValues, $newValues);
                    }

                    $updated++;

                    continue;
                }

                $estate = Estate::create($data);
                $estate->load(['location.area']);

                $this->logger->created($estate);

                $created++;
            }

            return [
                'created' => $created,
                'updated' => $updated,
            ];
        });

        $this->fieldSuggestions->invalidateCache();

        return $result;
    }
}
