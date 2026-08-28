<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seed a realistic PPOB catalog (pulsa/data/token-listrik/tagihan/internet)
 * focused on Indosat (Ooredoo) plus the major Indonesian operators so the
 * "beli pulsa" flow can be tested end-to-end.
 *
 * Idempotent & additive: skips operators/products that already exist
 * (unique key: ppob_operators.slug, ppob_products.provider_product_code).
 * Selling price is derived (provider + margin + admin_fee) and stored so the
 * catalog is consistent with the pass-through pricing path.
 */
final class PpobCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $adminId = $this->resolveAdminId();

        // operator: [slug, name, category, brand, operator_prefix, provider_name]
        $operators = [
            // ── PULSA ─────────────────────────────────────────────────────────
            ['indosat-pulsa', 'Indosat Ooredoo', 'pulsa', 'Indosat', '0814,0815,0816,0855,0856,0857,0858', 'IAK'],
            ['telkomsel-pulsa', 'Telkomsel', 'pulsa', 'Telkomsel', '0811,0812,0813,0821,0822,0823,0852,0853', 'IAK'],
            ['xl-pulsa', 'XL Axiata', 'pulsa', 'XL', '0817,0818,0819,0859,0877,0878', 'IAK'],
            ['tri-pulsa', 'Tri (3)', 'pulsa', 'Tri', '0895,0896,0897,0898', 'IAK'],
            ['axis-pulsa', 'Axis', 'pulsa', 'Axis', '0838,0831,0832,0833', 'IAK'],
            ['smartfren-pulsa', 'Smartfren', 'pulsa', 'Smartfren', '0881,0882,0883,0888', 'IAK'],
            ['byu-pulsa', 'by.U', 'pulsa', 'by.U', '0851', 'IAK'],
            // ── PAKET DATA ────────────────────────────────────────────────────
            ['indosat-data', 'Indosat Freedom', 'data', 'Indosat', '0814,0815,0816,0855,0856,0857,0858', 'IAK'],
            ['telkomsel-data', 'Telkomsel Seluler', 'data', 'Telkomsel', '0811,0812,0813,0821,0822,0823,0852,0853', 'IAK'],
            ['xl-data', 'XL Prioritas', 'data', 'XL', '0817,0818,0819,0859,0877,0878', 'IAK'],
            ['tri-data', 'Tri (3)', 'data', 'Tri', '0895,0896,0897,0898', 'IAK'],
            ['axis-data', 'Axis', 'data', 'Axis', '0838,0831,0832,0833', 'IAK'],
            ['smartfren-data', 'Smartfren', 'data', 'Smartfren', '0881,0882,0883,0888', 'IAK'],
            // ── TOKEN LISTRIK ─────────────────────────────────────────────────
            ['pln-token', 'PLN Token (Prepaid)', 'token-listrik', 'PLN', null, 'IAK'],
            // ── TAGIHAN ───────────────────────────────────────────────────────
            ['pln-tagihan', 'PLN Postpaid', 'tagihan', 'PLN', null, 'IAK'],
            ['pdam-tagihan', 'PDAM', 'tagihan', 'PDAM', null, 'IAK'],
            // ── INTERNET / WIFI ───────────────────────────────────────────────
            ['indihome-internet', 'IndiHome', 'internet', 'IndiHome', null, 'IAK'],
            ['firstmedia-internet', 'First Media', 'internet', 'First Media', null, 'IAK'],
            ['biznet-internet', 'Biznet Home', 'internet', 'Biznet', null, 'IAK'],
            // ── VOUCHER ───────────────────────────────────────────────────────
            ['game-voucher', 'Voucher Game', 'voucher', 'Game', null, 'IAK'],
        ];

        $operatorIdBySlug = [];
        foreach ($operators as $op) {
            [$slug, $name, $category, $brand, $prefix, $provider] = $op;
            $id = DB::table('ppob_operators')->where('slug', $slug)->value('id');
            if ($id) {
                $operatorIdBySlug[$slug] = (int) $id;
                continue;
            }

            $id = DB::table('ppob_operators')->insertGetId([
                'name' => $name,
                'slug' => $slug,
                'category' => $category,
                'brand' => $brand,
                'operator_prefix' => $prefix,
                'provider_name' => $provider,
                'icon_url' => $this->icon($brand),
                'is_active' => true,
                'created_by' => $adminId,
                'updated_by' => $adminId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $operatorIdBySlug[$slug] = $id;
        }

        // product: [operator_slug, category, product_type, product_code, name,
        //           nominal, provider_price, margin, admin_fee, commission]
        $products = [
            // Indosat pulsa (provider < nominal; selling = provider + 50 + 200)
            ['indosat-pulsa', 'pulsa', 'prepaid', 'PLSR5', 'Indosat 5.000', '5.000', 5350, 50, 200, 0],
            ['indosat-pulsa', 'pulsa', 'prepaid', 'PLSR10', 'Indosat 10.000', '10.000', 10200, 100, 200, 0],
            ['indosat-pulsa', 'pulsa', 'prepaid', 'PLSR12', 'Indosat 12.500', '12.500', 12600, 150, 200, 0],
            ['indosat-pulsa', 'pulsa', 'prepaid', 'PLSR15', 'Indosat 15.000', '15.000', 15100, 200, 200, 0],
            ['indosat-pulsa', 'pulsa', 'prepaid', 'PLSR20', 'Indosat 20.000', '20.000', 20100, 200, 300, 0],
            ['indosat-pulsa', 'pulsa', 'prepaid', 'PLSR25', 'Indosat 25.000', '25.000', 25100, 300, 300, 0],
            ['indosat-pulsa', 'pulsa', 'prepaid', 'PLSR50', 'Indosat 50.000', '50.000', 50000, 500, 500, 0],
            ['indosat-pulsa', 'pulsa', 'prepaid', 'PLSR100', 'Indosat 100.000', '100.000', 99000, 1000, 500, 0],
            // Telkomsel pulsa (pap)
            ['telkomsel-pulsa', 'pulsa', 'prepaid', 'TSEK5', 'Telkomsel 5.000', '5.000', 5500, 50, 200, 0],
            ['telkomsel-pulsa', 'pulsa', 'prepaid', 'TSEK10', 'Telkomsel 10.000', '10.000', 10300, 100, 200, 0],
            ['telkomsel-pulsa', 'pulsa', 'prepaid', 'TSEK20', 'Telkomsel 20.000', '20.000', 20200, 200, 300, 0],
            ['telkomsel-pulsa', 'pulsa', 'prepaid', 'TSEK25', 'Telkomsel 25.000', '25.000', 25200, 300, 300, 0],
            ['telkomsel-pulsa', 'pulsa', 'prepaid', 'TSEK50', 'Telkomsel 50.000', '50.000', 50100, 500, 400, 0],
            ['telkomsel-pulsa', 'pulsa', 'prepaid', 'TSEK100', 'Telkomsel 100.000', '100.000', 99100, 1000, 400, 0],
            // XL pulsa
            ['xl-pulsa', 'pulsa', 'prepaid', 'XLR5', 'XL 5.000', '5.000', 5450, 50, 200, 0],
            ['xl-pulsa', 'pulsa', 'prepaid', 'XLR10', 'XL 10.000', '10.000', 10300, 100, 200, 0],
            ['xl-pulsa', 'pulsa', 'prepaid', 'XLR15', 'XL 15.000', '15.000', 15200, 200, 300, 0],
            ['xl-pulsa', 'pulsa', 'prepaid', 'XLR20', 'XL 20.000', '20.000', 20200, 200, 300, 0],
            ['xl-pulsa', 'pulsa', 'prepaid', 'XLR50', 'XL 50.000', '50.000', 50100, 500, 400, 0],
            ['xl-pulsa', 'pulsa', 'prepaid', 'XLR100', 'XL 100.000', '100.000', 99000, 1000, 500, 0],
            // Tri pulsa
            ['tri-pulsa', 'pulsa', 'prepaid', 'TRI5', 'Tri 5.000', '5.000', 5400, 50, 200, 0],
            ['tri-pulsa', 'pulsa', 'prepaid', 'TRI10', 'Tri 10.000', '10.000', 10150, 100, 200, 0],
            ['tri-pulsa', 'pulsa', 'prepaid', 'TRI20', 'Tri 20.000', '20.000', 20100, 200, 300, 0],
            ['tri-pulsa', 'pulsa', 'prepaid', 'TRI50', 'Tri 50.000', '50.000', 49900, 500, 400, 0],
            // Axis pulsa
            ['axis-pulsa', 'pulsa', 'prepaid', 'AX5', 'Axis 5.000', '5.000', 5400, 50, 200, 0],
            ['axis-pulsa', 'pulsa', 'prepaid', 'AX10', 'Axis 10.000', '10.000', 10100, 100, 200, 0],
            ['axis-pulsa', 'pulsa', 'prepaid', 'AX20', 'Axis 20.000', '20.000', 20000, 200, 300, 0],
            ['axis-pulsa', 'pulsa', 'prepaid', 'AX50', 'Axis 50.000', '50.000', 49800, 500, 400, 0],
            // Smartfren pulsa
            ['smartfren-pulsa', 'pulsa', 'prepaid', 'SMR5', 'Smartfren 5.000', '5.000', 5450, 50, 200, 0],
            ['smartfren-pulsa', 'pulsa', 'prepaid', 'SMR10', 'Smartfren 10.000', '10.000', 10200, 100, 200, 0],
            ['smartfren-pulsa', 'pulsa', 'prepaid', 'SMR20', 'Smartfren 20.000', '20.000', 20100, 200, 300, 0],
            ['smartfren-pulsa', 'pulsa', 'prepaid', 'SMR50', 'Smartfren 50.000', '50.000', 50000, 500, 400, 0],
            // by.U pulsa
            ['byu-pulsa', 'pulsa', 'prepaid', 'BYU10', 'by.U 10.000', '10.000', 10200, 100, 200, 0],
            ['byu-pulsa', 'pulsa', 'prepaid', 'BYU25', 'by.U 25.000', '25.000', 25100, 300, 300, 0],
            // Indosat data (Freedom)
            ['indosat-data', 'data', 'prepaid', 'DF1GB', 'Indosat Freedom 1GB', '1 GB', 9900, 300, 400, 0],
            ['indosat-data', 'data', 'prepaid', 'DF2GB', 'Indosat Freedom 2GB', '2 GB', 14900, 300, 500, 0],
            ['indosat-data', 'data', 'prepaid', 'DF3GB', 'Indosat Freedom 3GB', '3 GB', 19000, 500, 500, 0],
            ['indosat-data', 'data', 'prepaid', 'DF5GB', 'Indosat Freedom 5GB', '5 GB', 25000, 500, 500, 0],
            ['indosat-data', 'data', 'prepaid', 'DF8GB', 'Indosat Freedom 8GB', '8 GB', 36000, 500, 600, 0],
            ['indosat-data', 'data', 'prepaid', 'DF10GB', 'Indosat Freedom 10GB', '10 GB', 40500, 500, 600, 0],
            ['indosat-data', 'data', 'prepaid', 'DF12GB', 'Indosat Freedom 12GB', '12 GB', 45500, 800, 700, 0],
            ['indosat-data', 'data', 'prepaid', 'DF20GB', 'Indosat Freedom 20GB', '20 GB', 67500, 1000, 900, 0],
            // Telkomsel data
            ['telkomsel-data', 'data', 'prepaid', 'TS1GB', 'Telkomsel Seluler 1GB', '1 GB', 10500, 300, 400, 0],
            ['telkomsel-data', 'data', 'prepaid', 'TS3GB', 'Telkomsel Seluler 3GB', '3 GB', 21000, 500, 500, 0],
            ['telkomsel-data', 'data', 'prepaid', 'TS5GB', 'Telkomsel Seluler 5GB', '5 GB', 28000, 500, 600, 0],
            ['telkomsel-data', 'data', 'prepaid', 'TS10GB', 'Telkomsel Seluler 10GB', '10 GB', 46000, 800, 800, 0],
            // XL data
            ['xl-data', 'data', 'prepaid', 'XL1GB', 'XL Prioritas 1GB', '1 GB', 10000, 300, 400, 0],
            ['xl-data', 'data', 'prepaid', 'XL5GB', 'XL Prioritas 5GB', '5 GB', 24000, 500, 500, 0],
            ['xl-data', 'data', 'prepaid', 'XL20GB', 'XL Prioritas 20GB', '20 GB', 62000, 1000, 900, 0],
            // Tri data
            ['tri-data', 'data', 'prepaid', 'TRD1GB', 'Tri 1GB', '1 GB', 9500, 300, 400, 0],
            ['tri-data', 'data', 'prepaid', 'TRD8GB', 'Tri 8GB', '8 GB', 30000, 600, 600, 0],
            // Axis data
            ['axis-data', 'data', 'prepaid', 'AXD1GB', 'Axis 1GB', '1 GB', 9800, 300, 400, 0],
            ['axis-data', 'data', 'prepaid', 'AXD5GB', 'Axis 5GB', '5 GB', 23000, 500, 500, 0],
            // Smartfren data
            ['smartfren-data', 'data', 'prepaid', 'SMD1GB', 'Smartfren 1GB', '1 GB', 9900, 300, 400, 0],
            ['smartfren-data', 'data', 'prepaid', 'SMD5GB', 'Smartfren 5GB', '5 GB', 26000, 500, 500, 0],
            // PLN token (prepaid) - selling = nominal premium + admin
            ['pln-token', 'token-listrik', 'prepaid', 'TOK20', 'Token PLN 20.000', '20.000', 19900, 0, 2400, 200],
            ['pln-token', 'token-listrik', 'prepaid', 'TOK50', 'Token PLN 50.000', '50.000', 49900, 0, 2600, 200],
            ['pln-token', 'token-listrik', 'prepaid', 'TOK100', 'Token PLN 100.000', '100.000', 99900, 0, 2800, 200],
            ['pln-token', 'token-listrik', 'prepaid', 'TOK200', 'Token PLN 200.000', '200.000', 199900, 0, 3200, 200],
            ['pln-token', 'token-listrik', 'prepaid', 'TOK500', 'Token PLN 500.000', '500.000', 499900, 0, 3500, 200],
            // PLN tagihan (postpaid) - amount via inquiry, admin charge as admin fee
            ['pln-tagihan', 'tagihan', 'postpaid', 'PLNPB', 'Pembayaran Tagihan Listrik PLN', null, 0, 0, 2500, 0],
            // PDAM tagihan (postpaid)
            ['pdam-tagihan', 'tagihan', 'postpaid', 'PDAMPB', 'Pembayaran Tagihan PDAM', null, 0, 0, 2000, 0],
            // IndiHome internet (postpaid)
            ['indihome-internet', 'internet', 'postpaid', 'IH30', 'IndiHome 30 Mbps', '30 Mbps', 285000, 0, 3000, 0],
            ['indihome-internet', 'internet', 'postpaid', 'IH50', 'IndiHome 50 Mbps', '50 Mbps', 360000, 0, 3000, 0],
            // First Media internet
            ['firstmedia-internet', 'internet', 'postpaid', 'FM100', 'First Media 100 Mbps', '100 Mbps', 350000, 0, 3000, 0],
            // Biznet
            ['biznet-internet', 'internet', 'postpaid', 'BZ50', 'Biznet 50 Mbps', '50 Mbps', 250000, 0, 3000, 0],
            // Voucher game
            ['game-voucher', 'voucher', 'prepaid', 'VCR10', 'Voucher Game 10.000', '10.000', 10150, 100, 200, 0],
            ['game-voucher', 'voucher', 'prepaid', 'VCR50', 'Voucher Game 50.000', '50.000', 49900, 500, 300, 0],
        ];

        $inserted = 0;
        foreach ($products as $p) {
            [$opSlug, $category, $type, $code, $name, $nominal, $provider, $margin, $admin, $commission] = $p;
            $operatorId = $operatorIdBySlug[$opSlug] ?? null;
            $selling = round($provider + $margin + $admin, 2);

            $exists = DB::table('ppob_products')->where('provider_product_code', $code)->exists();
            if ($exists) {
                continue;
            }

            DB::table('ppob_products')->insert([
                'operator_id' => $operatorId,
                'category' => $category,
                'product_type' => $type,
                'provider_product_code' => $code,
                'name' => $name,
                'brand' => $this->brandOf($name),
                'nominal' => $nominal,
                'provider_price' => $provider,
                'admin_fee' => $admin,
                'commission' => $commission,
                'margin' => $margin,
                'selling_price' => $selling,
                'status' => 'active',
                'is_available' => true,
                'icon_url' => $this->icon($this->brandOf($name)),
                'is_active' => true,
                'created_by' => $adminId,
                'updated_by' => $adminId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $inserted++;
        }

        // No global pricing rule: products use pass-through (stored margin +
        // admin_fee), so selling_price = provider + margin + admin_fee stays
        // deterministic and realistic for testing "beli pulsa".

        $this->command?->info(
            "PPOB catalog seeded: ".count($operatorIdBySlug)." operators, {$inserted} new products."
        );
    }

    private function resolveAdminId(): ?string
    {
        $id = SeederIds::SUPER_ADMIN;
        if (DB::table('users')->where('id', $id)->exists()) {
            return $id;
        }

        return DB::table('users')->whereNotNull('id')->value('id');
    }

    private function brandOf(string $name): string
    {
        return trim(explode(' ', $name)[0]) ?: $name;
    }

    private function icon(string $brand): ?string
    {
        if (! $brand) {
            return null;
        }

        return 'https://picsum.photos/seed/ppob-'.strtolower(str_replace(' ', '-', $brand)).'/128/128';
    }
}
