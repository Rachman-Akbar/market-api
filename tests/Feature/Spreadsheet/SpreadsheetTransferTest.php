<?php

declare(strict_types=1);

namespace Tests\Feature\Spreadsheet;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\InteractsAsUser;
use Tests\TestCase;

class SpreadsheetTransferTest extends TestCase
{
    use InteractsAsUser;
    use RefreshDatabase;

    public function test_admin_can_download_template_for_import_enabled_module(): void
    {
        $this->actingAsRole('admin');

        $this->get('/api/v1/catalog/spreadsheets/voucher/template')->assertOk();
    }

    public function test_admin_can_export_module_rows(): void
    {
        $this->actingAsRole('admin');

        $this->post('/api/v1/catalog/spreadsheets/voucher/export', [
            'ids' => [],
        ])->assertOk();
    }

    public function test_preview_import_requires_a_file(): void
    {
        $this->actingAsRole('admin');

        $this->postJson('/api/v1/catalog/spreadsheets/voucher/import/preview', [
            'import_mode' => 'create',
        ])->assertStatus(422);
    }

    public function test_import_requires_a_file(): void
    {
        $this->actingAsRole('admin');

        $this->postJson('/api/v1/catalog/spreadsheets/voucher/import', [
            'import_mode' => 'create',
        ])->assertStatus(422);
    }

    public function test_buyer_is_blocked_from_spreadsheets(): void
    {
        $this->actingAsRole('buyer');

        $this->get('/api/v1/catalog/spreadsheets/voucher/template')->assertForbidden();
        $this->post('/api/v1/catalog/spreadsheets/voucher/export', [])->assertForbidden();
    }
}
