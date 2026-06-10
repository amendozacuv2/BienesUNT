<?php

namespace Tests\Feature\Estates;

use App\Models\User;
use App\Services\Estates\EstateFieldSuggestions;
use Mockery;
use Tests\TestCase;

class EstateFieldSuggestionEndpointTest extends TestCase
{
    public function test_endpoint_requires_authentication(): void
    {
        $this->getJson(route('estates.field-options', [
            'field' => 'brand',
            'term' => 'toy',
        ]))->assertUnauthorized();
    }

    public function test_endpoint_returns_select2_results_for_authorized_user(): void
    {
        $suggestions = Mockery::mock(EstateFieldSuggestions::class);
        $suggestions->shouldReceive('allowedFields')
            ->once()
            ->andReturn(['denomination', 'brand', 'model', 'type', 'color']);
        $suggestions->shouldReceive('forSelect2')
            ->once()
            ->with('brand', 'toy', 20)
            ->andReturn([
                ['id' => 'TOYOTA', 'text' => 'TOYOTA', 'total' => 42],
            ]);

        $this->app->instance(EstateFieldSuggestions::class, $suggestions);

        $this->actingAs($this->authorizedUser())
            ->getJson(route('estates.field-options', [
                'field' => 'brand',
                'term' => 'toy',
            ]))
            ->assertOk()
            ->assertExactJson([
                'results' => [
                    ['id' => 'TOYOTA', 'text' => 'TOYOTA', 'total' => 42],
                ],
            ]);
    }

    public function test_endpoint_rejects_unknown_fields(): void
    {
        $suggestions = Mockery::mock(EstateFieldSuggestions::class);
        $suggestions->shouldReceive('allowedFields')
            ->once()
            ->andReturn(['denomination', 'brand', 'model', 'type', 'color']);
        $suggestions->shouldNotReceive('forSelect2');

        $this->app->instance(EstateFieldSuggestions::class, $suggestions);

        $this->actingAs($this->authorizedUser())
            ->getJson(route('estates.field-options', [
                'field' => 'internal_code',
                'term' => 'bien',
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('field');
    }

    public function test_endpoint_rejects_user_without_create_or_edit_permission(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->id = 2;
        $user->exists = true;
        $user->shouldReceive('getAuthIdentifier')->andReturn(2);
        $user->shouldReceive('can')->with('create.estate')->andReturnFalse();
        $user->shouldReceive('can')->with('edit.estate')->andReturnFalse();

        $this->actingAs($user)
            ->getJson(route('estates.field-options', [
                'field' => 'brand',
                'term' => 'toy',
            ]))
            ->assertForbidden();
    }

    private function authorizedUser(): User
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->id = 1;
        $user->exists = true;
        $user->shouldReceive('getAuthIdentifier')->andReturn(1);
        $user->shouldReceive('can')->with('create.estate')->andReturnTrue();

        return $user;
    }
}
