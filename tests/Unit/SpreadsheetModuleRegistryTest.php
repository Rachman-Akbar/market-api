<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domains\Shared\Spreadsheet\Application\SpreadsheetModuleRegistry;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SpreadsheetModuleRegistryTest extends TestCase
{
    #[DataProvider('advancedModules')]
    public function test_advanced_module_uses_product_workbook_contract(string $module): void
    {
        $config = SpreadsheetModuleRegistry::get($module);

        $this->assertNotEmpty($config['label']);
        $this->assertContains('admin', $config['roles']);
        $this->assertContains('seller', $config['roles']);
        $this->assertNotEmpty($config['headers']);
        $this->assertCount(10, $config['examples']);
        $this->assertCount(count($config['headers']), $config['descriptions']);
        $this->assertSame($config['headers'], array_column($config['descriptions'], 0));
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
