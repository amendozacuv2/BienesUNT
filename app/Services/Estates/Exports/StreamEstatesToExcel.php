<?php

namespace App\Services\Estates\Exports;

use Illuminate\Support\Collection;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\AutoFilter;
use OpenSpout\Writer\Common\Entity\Sheet;
use OpenSpout\Writer\XLSX\Entity\SheetView;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Options\SheetProtection;
use OpenSpout\Writer\XLSX\Writer;

class StreamEstatesToExcel
{
    private const HEADINGS = [
        'COD. PATRIMONIAL',
        'COD. INTERNO',
        'DENOMINACIÓN',
        'MARCA',
        'MODELO',
        'TIPO',
        'COLOR',
        'SERIE',
        'DIMENSIONES',
        'OTRO',
        'SITUACIÓN',
        'ESTADO DE CONSERVACIÓN',
        'OBSERVACIONES',
        'UBICACIÓN',
        'ÁREA',
        'ENCARGADO DE ÁREA',
        'CREADO',
        'ACTUALIZADO',
    ];

    private const COLUMN_WIDTHS = [
        1 => 20,
        2 => 20,
        3 => 32,
        4 => 20,
        5 => 20,
        6 => 20,
        7 => 16,
        8 => 22,
        9 => 26,
        10 => 26,
        11 => 18,
        12 => 26,
        13 => 36,
        14 => 28,
        15 => 28,
        16 => 36,
        17 => 20,
        18 => 20,
    ];

    public function __construct(
        private readonly EstateExportQuery $exportQuery,
        private readonly EstateExportRowMapper $rowMapper
    ) {}

    public function write(
        array $allowedAreaIds,
        array $filters,
        string $outputPath = 'php://output'
    ): void {
        $writer = new Writer(new Options);
        $writer->openToFile($outputPath);

        $sheetNumber = 1;
        $dataRowsInSheet = 0;
        $sheet = $this->startSheet($writer, $sheetNumber);

        try {
            $this->exportQuery
                ->build(
                    allowedAreaIds: $allowedAreaIds,
                    search: $filters['search'],
                    areaId: $filters['areaId'],
                    locationId: $filters['locationId'],
                    situation: $filters['situation'],
                    conservationStatus: $filters['conservationStatus']
                )
                ->chunkByIdDesc(
                    $this->chunkSize(),
                    function (Collection $estates) use (
                        $writer,
                        &$sheet,
                        &$sheetNumber,
                        &$dataRowsInSheet
                    ) {
                        foreach ($estates as $estate) {
                            if ($dataRowsInSheet >= $this->rowsPerSheet()) {
                                $this->finishSheet($sheet, $dataRowsInSheet);
                                $sheet = $this->startSheet($writer, ++$sheetNumber);
                                $dataRowsInSheet = 0;
                            }

                            $writer->addRow(Row::fromValues($this->rowMapper->map($estate)));
                            $dataRowsInSheet++;
                        }
                    },
                    'estates.id',
                    'id'
                );

            $this->finishSheet($sheet, $dataRowsInSheet);
        } finally {
            $writer->close();
        }
    }

    private function startSheet(Writer $writer, int $sheetNumber): Sheet
    {
        $sheet = $sheetNumber === 1
            ? $writer->getCurrentSheet()
            : $writer->addNewSheetAndMakeItCurrent();

        $sheet->setName('Bienes '.$sheetNumber);
        $sheet->setSheetView((new SheetView)->setFreezeRow(2));
        $sheet->setSheetProtection(new SheetProtection(
            lockSheet: true,
            lockColumnInsert: true,
            lockColumnDelete: true,
            lockColumnFormatting: true,
            lockRowInsert: true,
            lockRowDelete: true
        ));

        foreach (self::COLUMN_WIDTHS as $column => $width) {
            $sheet->setColumnWidth($width, $column);
        }

        $writer->addRow(Row::fromValues(self::HEADINGS, $this->headingStyle()));

        return $sheet;
    }

    private function finishSheet(Sheet $sheet, int $dataRows): void
    {
        $sheet->setAutoFilter(new AutoFilter(
            fromColumnIndex: 0,
            fromRow: 1,
            toColumnIndex: count(self::HEADINGS) - 1,
            toRow: max(1, $dataRows + 1)
        ));
    }

    private function headingStyle(): Style
    {
        return (new Style)
            ->setFontBold()
            ->setFontColor(Color::WHITE)
            ->setBackgroundColor(Color::BLUE)
            ->setShouldWrapText();
    }

    private function rowsPerSheet(): int
    {
        return max(1, min(
            (int) config('estates.exports.rows_per_sheet', 200000),
            1048575
        ));
    }

    private function chunkSize(): int
    {
        return max(100, (int) config('estates.exports.chunk_size', 2000));
    }
}
