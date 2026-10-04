<?php

declare(strict_types=1);

namespace Tests\Feature\Shared;

use App\Domains\Shared\Codes\Application\Services\CodePatternFormatter;
use App\Domains\Shared\Codes\Application\Services\CodePatternService;
use App\Domains\Identity\User\Domain\Entities\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Support\InteractsAsUser;
use Tests\TestCase;

final class CodePatternTest extends TestCase
{
    use InteractsAsUser;
    use RefreshDatabase;

    private function makeStoreFor(string $name): int
    {
        $user = $this->makeUser([], ['seller']);

        return (int) $this->makeStore($user, ['name' => $name, 'slug' => Str::slug($name).'-'.Str::random(6)])->id;
    }

    public function test_rumus_default_menghasilkan_kode_sesuai_format_sebelumnya(): void
    {
        $render = fn (string $type, array $context = []) => CodePatternFormatter::render(CodePatternFormatter::DEFAULTS[$type], $context + ['type' => $type]);

        $this->assertMatchesRegularExpression('/^RM-[A-Z0-9]{1,6}$/', $render('material_code', ['name' => 'Gula Pasir']));
        $this->assertMatchesRegularExpression('/^MAN-\d{14}-[A-Z0-9]{6}$/', $render('order_number'));
        $this->assertMatchesRegularExpression('/^INV\/\w{3}\/\d{6}\/[A-Z0-9]{4}$/', $render('invoice_reference', ['type' => 'receivable']));
        $this->assertMatchesRegularExpression('/^STK-MASUK-\d{6}-\d{3}$/', $render('stock_reference', ['movement' => 'MASUK']));
    }

    public function test_token_panjang_dan_urutan_bisa_kustom(): void
    {
        $this->assertSame('SKU-GULA-0007', CodePatternFormatter::render('{type}-{name:4}-{seq:4}', ['type' => 'SKU', 'name' => 'Gula Pasir', 'seq' => 7]));
        $this->assertSame('0001-1', CodePatternFormatter::render('{seq}-{seq:1}', ['seq' => 1]));
    }

    public function test_token_kosong_diisi_placeholders(): void
    {
        $this->assertSame('RM-X-1', CodePatternFormatter::render('RM-{name}-{seq:1}', ['name' => '', 'seq' => 1]));
    }

    public function test_rumus_kustom_disimpan_dan_dipakai(): void
    {
        $storeId = $this->makeStoreFor('Toko uji');
        $service = app(CodePatternService::class);
        $service->save(['material_code' => 'BB-{store:3}-{name:5}-{seq:3}'], $storeId);

        $code = $service->unique('material_code', $storeId, ['name' => 'Gula Pasir', 'store' => 'Ziip Store', 'seq' => 1], fn (): bool => false);

        $this->assertSame('BB-ZII-GULAP-001', $code);
        $this->assertSame('BB-{STORE:3}-{NAME:5}-{SEQ:3}', $service->patternFor('material_code', $storeId));
    }

    public function test_rumus_toko_override_rumus_global(): void
    {
        $firstStore = $this->makeStoreFor('Toko satu');
        $secondStore = $this->makeStoreFor('Toko dua');
        $service = app(CodePatternService::class);
        $service->save(['material_code' => 'GLOBAL-{name:4}'], null);
        $service->save(['material_code' => 'TOKO-{name:4}'], $secondStore);

        $this->assertSame('GLOBAL-{NAME:4}', $service->patternFor('material_code', $firstStore));
        $this->assertSame('TOKO-{NAME:4}', $service->patternFor('material_code', $secondStore));
    }

    public function test_rumus_kosong_kembali_ke_default(): void
    {
        $storeId = $this->makeStoreFor('Toko reset');
        $service = app(CodePatternService::class);
        $service->save(['material_code' => 'X-{seq:2}'], $storeId);
        $service->save(['material_code' => ''], $storeId);

        $this->assertSame(CodePatternFormatter::DEFAULTS['material_code'], $service->patternFor('material_code', $storeId));
    }

    public function test_unique_menaikkan_urutan_sampai_bebas(): void
    {
        $service = app(CodePatternService::class);
        $taken = ['KODE-0001', 'KODE-0002'];
        $service->save(['material_code' => 'KODE-{seq:4}'], null);

        $code = $service->unique('material_code', null, ['type' => 'material_code', 'name' => '', 'seq' => 1], fn (string $candidate): bool => in_array($candidate, $taken, true), 10);

        $this->assertSame('KODE-0003', $code);
    }
}
