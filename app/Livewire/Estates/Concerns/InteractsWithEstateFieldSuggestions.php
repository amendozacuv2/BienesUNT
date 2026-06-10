<?php

namespace App\Livewire\Estates\Concerns;

use App\Services\Estates\EstateFieldSuggestions;
use Livewire\Attributes\Renderless;

trait InteractsWithEstateFieldSuggestions
{
    #[Renderless]
    public function searchEstateFieldOptions(string $field, ?string $term = null): array
    {
        $suggestions = app(EstateFieldSuggestions::class);

        if (! in_array($field, $suggestions->allowedFields(), true)) {
            return [];
        }

        return $suggestions->forSelect2($field, $term, 20);
    }
}
