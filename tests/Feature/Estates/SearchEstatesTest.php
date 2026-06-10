<?php

namespace Tests\Feature\Estates;

use App\Models\Estate;
use App\Services\Estates\SearchEstates;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SearchEstatesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('estates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('location_id');
            $table->string('internal_code')->unique();
            $table->string('patrimonial_code')->nullable();
            $table->string('denomination')->nullable();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('type')->nullable();
            $table->string('color')->nullable();
            $table->text('observation')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('estates');

        parent::tearDown();
    }

    public function test_live_search_finds_each_supported_field_case_insensitively(): void
    {
        $searchableValues = [
            'patrimonial_code' => 'PAT-ALPHA',
            'internal_code' => 'INT-BRAVO',
            'denomination' => 'DENOMINATION CHARLIE',
            'brand' => 'BRAND DELTA',
            'model' => 'MODEL ECHO',
            'type' => 'TYPE FOXTROT',
            'observation' => 'OBSERVATION GOLF',
        ];

        foreach ($searchableValues as $field => $value) {
            $row = $this->estateRow('ROW-'.$field);
            $row[$field] = $value;

            DB::table('estates')->insert($row);
        }

        foreach ($searchableValues as $field => $value) {
            $term = mb_strtolower(str($value)->afterLast(' ')->toString());
            $expectedCode = $field === 'internal_code' ? $value : 'ROW-'.$field;

            $this->assertSame(
                [$expectedCode],
                Estate::query()->liveSearch($term)->pluck('internal_code')->all()
            );
        }
    }

    public function test_live_search_preserves_the_current_two_character_minimum(): void
    {
        DB::table('estates')->insert($this->estateRow('ROW-ONE'));
        DB::table('estates')->insert($this->estateRow('ROW-TWO'));

        $this->assertSame(2, Estate::query()->liveSearch('x')->count());
    }

    public function test_search_service_uses_the_same_expression_as_the_model_scope(): void
    {
        $query = app(SearchEstates::class)->forIndex(
            [1],
            'alpha',
            null,
            null,
            null,
            null
        );

        $sql = preg_replace('/\s+/', ' ', $query->toSql());
        $expression = preg_replace('/\s+/', ' ', Estate::LIVE_SEARCH_EXPRESSION);

        $this->assertStringContainsString($expression.' LIKE ?', $sql);
        $this->assertStringNotContainsString(' OR ', strtoupper($sql));
        $this->assertSame(['alpha'], [trim($query->getBindings()[1], '%')]);
    }

    private function estateRow(string $internalCode): array
    {
        return [
            'location_id' => 1,
            'internal_code' => $internalCode,
            'patrimonial_code' => null,
            'denomination' => null,
            'brand' => null,
            'model' => null,
            'type' => null,
            'color' => null,
            'observation' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
