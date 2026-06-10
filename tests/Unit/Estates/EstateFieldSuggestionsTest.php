<?php

namespace Tests\Unit\Estates;

use App\Livewire\Estates\Concerns\InteractsWithEstateFieldSuggestions;
use App\Services\Estates\EstateFieldSuggestions;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Renderless;
use ReflectionClass;
use Tests\TestCase;

class EstateFieldSuggestionsTest extends TestCase
{
    public function test_only_expected_fields_are_allowed(): void
    {
        $this->assertSame(
            ['denomination', 'brand', 'model', 'type', 'color'],
            app(EstateFieldSuggestions::class)->allowedFields()
        );
    }

    public function test_search_term_normalization_trims_and_collapses_spaces(): void
    {
        $service = app(EstateFieldSuggestions::class);
        $method = (new ReflectionClass($service))->getMethod('normalizeTerm');

        $this->assertSame('TOYOTA HILUX', $method->invoke($service, "  TOYOTA \n  HILUX  "));
        $this->assertSame('', $method->invoke($service, null));
    }

    public function test_cache_invalidation_advances_the_suggestion_version(): void
    {
        Cache::forget('estates:field-suggestions:version');

        app(EstateFieldSuggestions::class)->invalidateCache();

        $this->assertSame(2, Cache::get('estates:field-suggestions:version'));
    }

    public function test_livewire_fallback_action_is_renderless(): void
    {
        $method = (new ReflectionClass(InteractsWithEstateFieldSuggestions::class))
            ->getMethod('searchEstateFieldOptions');

        $this->assertCount(1, $method->getAttributes(Renderless::class));
    }
}
