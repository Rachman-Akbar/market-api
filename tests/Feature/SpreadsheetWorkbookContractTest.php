<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Shared\Spreadsheet\Application\SpreadsheetModuleRegistry;
use App\Domains\Shared\Spreadsheet\Presentation\Http\Controllers\SpreadsheetTransferController;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionMethod;
use Tests\TestCase;

final class SpreadsheetWorkbookContractTest extends TestCase
{
    #[DataProvider('advancedModules')]
    public function test_template_uses_same_three_sheet_contract_as_product(string $module): void
    {
        $config = SpreadsheetModuleRegistry::get($module);
        $controller = $this->app->make(SpreadsheetTransferController::class);
        $method = new ReflectionMethod($controller, 'createTemplateWorkbook');
        $workbook = $method->invoke($controller, $config);

        $this->assertInstanceOf(Spreadsheet::class, $workbook);
        $this->assertSame(['Template Kosong', 'Contoh Kasus Import', 'Penjelasan Kolom'], $workbook->getSheetNames());

        $sheet = $workbook->getSheetByName('Template Kosong');
        $headers = [];
        foreach ($config['headers'] as $index => $header) {
            $column = Coordinate::stringFromColumnIndex($index + 1);
            $headers[] = $sheet->getCell($column.'1')->getValue();
        }

        $this->assertSame($config['headers'], $headers);
        $this->assertStringContainsString('10 CONTOH KASUS IMPORT', (string) $workbook->getSheetByName('Contoh Kasus Import')->getCell('A1')->getValue());
        $this->assertSame('PANDUAN MODUL', $workbook->getSheetByName('Penjelasan Kolom')->getCell('A1')->getValue());

        $workbook->disconnectWorksheets();
    }

    public static function advancedModules(): array
    {
        return [
            ['order'],
            ['income'],
            ['expense'],
            ['receivable'],
            ['payable'],
            ['stock'],
        ];
    }
}
