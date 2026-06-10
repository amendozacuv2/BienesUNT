<?php

namespace Tests\Unit\Estates;

use App\Services\Estates\EstateFormRowsService;
use App\Services\Estates\NormalizeEstateData;
use Tests\TestCase;

class EstateSelect2BehaviorTest extends TestCase
{
    public function test_new_rows_keep_optional_select2_fields_empty(): void
    {
        $row = app(EstateFormRowsService::class)->emptyRow();

        foreach ([
            'denomination',
            'brand',
            'model',
            'type',
            'color',
            'series',
            'dimensions',
            'others',
        ] as $field) {
            $this->assertSame('', $row[$field]);
        }
    }

    public function test_empty_optional_select2_values_are_saved_as_null_not_no_aplica(): void
    {
        $data = app(NormalizeEstateData::class)->fromRow([
            'internal_code' => 'BIEN-TEST',
            'denomination' => '',
            'brand' => '',
            'model' => '',
            'type' => '',
            'color' => '',
            'series' => '',
            'dimensions' => '',
            'others' => '',
        ], 1);

        foreach ([
            'denomination',
            'brand',
            'model',
            'type',
            'color',
            'series',
            'dimensions',
            'others',
        ] as $field) {
            $this->assertNull($data[$field]);
        }
    }

    public function test_no_aplica_is_only_saved_when_explicitly_selected(): void
    {
        $normalizer = app(NormalizeEstateData::class);

        $this->assertNull($normalizer->cleanText(''));
        $this->assertSame('NO APLICA', $normalizer->cleanText('NO APLICA'));
    }
}
