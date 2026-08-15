<?php

declare(strict_types=1);

namespace App\Domains\Shared\Spreadsheet\Application;

use App\Domains\Catalog\Banner\Infrastructure\Persistence\Models\BannerModel;
use App\Domains\Catalog\CatalogGroup\Infrastructure\Persistence\Models\CatalogGroupModel;
use App\Domains\Catalog\Category\Infrastructure\Persistence\Models\CategoryModel;
use App\Domains\Catalog\Product\Costing\Infrastructure\Persistence\Models\ProductCostingImpactModel;
use App\Domains\Catalog\Product\Costing\Infrastructure\Persistence\Models\ProductCostingModel;
use App\Domains\Catalog\Product\Infrastructure\Persistence\Models\ProductModel;
use App\Domains\Catalog\Promotion\Infrastructure\Persistence\Models\PromotionModel;
use App\Domains\Order\Voucher\Domain\Entities\Voucher;
use App\Domains\Order\Ordering\Infrastructure\Persistence\Models\OrderModel;
use App\Domains\Order\Review\Infrastructure\Persistence\Models\ProductReviewModel;
use App\Domains\Seller\Finance\Infrastructure\Persistence\Models\FinancialTransactionModel;
use App\Domains\Seller\Inventory\Infrastructure\Persistence\Models\RawMaterialModel;
use App\Domains\Seller\Inventory\Infrastructure\Persistence\Models\RawMaterialStockMovementModel;
use App\Domains\Identity\User\Domain\Entities\User;
use App\Domains\Seller\Stock\Infrastructure\Persistence\Models\StockMovementModel;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

final class SpreadsheetModuleRegistry
{
    public static function get(string $module): array
    {
        $key = strtolower(trim($module));
        $modules = self::modules();

        if (! isset($modules[$key])) {
            throw new InvalidArgumentException('Modul import/export tidak didukung.');
        }

        return $modules[$key];
    }

    public static function assertRoleAllowed(array $config, string $role): void
    {
        if (! in_array($role, $config['roles'], true)) {
            throw new InvalidArgumentException('Role tidak memiliki akses ke modul import/export ini.');
        }
    }

    public static function model(array $config): Model
    {
        $class = $config['model'];
        return new $class();
    }

    public static function modules(): array
    {
        return [
            'product' => [
                'label' => 'Product',
                'model' => ProductModel::class,
                'roles' => ['admin', 'seller'],
                'image_fields' => ['thumbnail', 'image_url'],
                'headers' => [
                    'id', 'store_name', 'catalog_group_name', 'name', 'slug', 'description', 'brand', 'primary_category_name', 'category_names', 'status', 'is_active', 'thumbnail', 'image_url', 'image_alt', 'sku', 'variant_name', 'price', 'is_default',
                ],
                'examples' => self::productExamples(),
                'guides' => [
                    ['Produk dijual tanpa pilihan', 'Isi satu baris variant bernama Default, price, dan is_default=1. SKU boleh dikosongkan karena backend akan membentuk SKU unik otomatis. Stok dikelola terpisah melalui modul Stok.'],
                    ['Produk benar-benar tanpa variant', 'Kosongkan sku, variant_name, price, dan is_default. Hanya data Product yang dibuat. Produk belum dapat dibeli sampai variant ditambahkan.'],
                    ['Produk dengan banyak variant', 'Gunakan beberapa baris dengan store_name dan name yang sama atau ID Product yang sama. Ulangi seluruh data utama Product. Gunakan SKU berbeda untuk tiap variant dan hanya satu is_default=1.'],
                    ['Mode Import Data Baru', 'Pilih Import Data Baru pada frontend. Kolom id harus kosong. Product yang telah ada akan ditolak, bukan diperbarui. Beberapa baris dengan nama Product yang sama hanya dipakai untuk membentuk multi-variant dalam file yang sama.'],
                    ['Mode Import Update Data', 'Pilih Import Update Data pada frontend dan isi id Product. SKU lama memperbarui variant terkait; SKU baru atau SKU kosong dapat menambah variant baru dengan SKU otomatis.'],
                    ['SKU', 'SKU optional. Jika kosong, backend membuat SKU unik. Jika SKU manual sudah digunakan pada toko yang sama atau kembar di file, baris dianggap error.'],
                    ['Category dan Catalog Group', 'Gunakan nama relasi, bukan ID. Jika belum ada, preview import akan menaruh permintaan pembuatan relasi pada tab Antrean untuk dipilih Lanjutkan atau Batal.'],
                    ['Gambar', 'Isi URL/path pada thumbnail atau image_url, atau tempel gambar pada cell/baris yang sama. File akan disimpan ke storage Laravel saat import.'],
                    ['Status marketplace', 'Agar tampil kepada Buyer, gunakan status=published dan is_active=1. Product juga tetap mengikuti status toko dan relasi publik.'],
                ],
                'descriptions' => self::productDescriptions(),
            ],
            'order' => [
                'label' => 'Pesanan',
                'model' => OrderModel::class,
                'roles' => ['admin', 'seller'],
                'image_fields' => [],
                'headers' => ['id', 'order_number', 'buyer_email', 'store_name', 'sub_order_number', 'order_type', 'status', 'payment_status', 'payment_method', 'shipping_address', 'sku', 'product_name', 'variant_name', 'quantity', 'price', 'shipping_cost', 'courier', 'service', 'destination_id', 'tracking_number', 'preorder_release_at', 'booking_expires_at', 'received_at'],
                'examples' => self::orderExamples(),
                'guides' => [
                    ['Satu pesanan banyak item', 'Gunakan order_number dan store_name yang sama pada beberapa baris. Setiap baris mewakili satu SKU.'],
                    ['Mode Data Baru', 'Kolom id kosong. Nomor pesanan baru dapat diulang di file yang sama untuk menambahkan item.'],
                    ['Mode Update Data', 'Isi id Order pada setiap baris. SKU yang sama memperbarui item, SKU baru menambah item pada sub-order toko tersebut.'],
                    ['Hak Seller', 'Seller otomatis dibatasi pada toko sesi. Seller tidak dapat mengimport item dari toko lain.'],
                    ['Tipe Pesanan', 'order_type menerima normal, preorder, atau booking. Isi tanggal khusus sesuai tipe.'],
                ],
                'descriptions' => self::orderDescriptions(),
            ],
            'income' => self::financeModule('Pemasukan', 'income'),
            'expense' => self::financeModule('Pengeluaran', 'expense'),
            'receivable' => self::financeModule('Piutang', 'receivable'),
            'payable' => self::financeModule('Hutang', 'payable'),
            'stock' => [
                'label' => 'Stok',
                'model' => StockMovementModel::class,
                'roles' => ['admin', 'seller'],
                'image_fields' => [],
                'headers' => ['id', 'store_name', 'sku', 'product_name', 'variant_name', 'movement_type', 'quantity_delta', 'balance_after', 'reference_type', 'reference_id', 'order_number', 'notes', 'occurred_at'],
                'examples' => self::stockExamples(),
                'guides' => [
                    ['Barang masuk / produksi', 'Isi movement_type=in dan quantity_delta positif. Jika Product mempunyai resep HPP, backend otomatis mengurangi bahan baku sesuai quantity resep.'],
                    ['Barang keluar', 'Isi movement_type=out dan quantity_delta negatif. Stok akhir tidak boleh negatif.'],
                    ['Penyesuaian', 'Gunakan movement_type=adjustment. Pada mode update, balance_after menjadi target stok terbaru dan sistem membuat movement koreksi.'],
                    ['SKU', 'SKU wajib dan harus milik toko yang dipilih atau toko sesi Seller.'],
                    ['Audit stok', 'Import tidak mengubah history lama. Setiap perubahan selalu membuat Stock Movement baru.'],
                ],
                'descriptions' => self::stockDescriptions(),
            ],
            'raw-material' => [
                'label' => 'Bahan Baku',
                'model' => RawMaterialModel::class,
                'roles' => ['admin', 'seller'],
                'image_fields' => [],
                'bulk_delete_enabled' => false,
                'headers' => ['id', 'store_name', 'code', 'name', 'unit', 'minimum_stock', 'average_cost', 'is_active'],
                'examples' => self::rawMaterialExamples(),
                'guides' => [
                    ['Master terpisah dari stok', 'Import Bahan Baku mengelola kode, nama, satuan, minimum stok, status, dan biaya awal saat data baru. Kuantitas serta perubahan biaya setelah bahan tersedia dikelola melalui Stok Bahan Baku.'],
                    ['Stok bahan baku', 'Gunakan modul Import Stok Bahan Baku untuk restock atau pemakaian. Master yang belum ada harus dibuat lebih dahulu.'],
                    ['Biaya rata-rata', 'average_cost hanya boleh diisi sebagai biaya awal saat membuat bahan baru. Setelah bahan tersedia, perubahan biaya dilakukan melalui Restock pada modul Stok Bahan Baku agar weighted average dan laporan dampak HPP tercatat.'],
                    ['Hak Seller', 'Seller otomatis menggunakan toko dari sesi dan tidak dapat memindahkan bahan baku ke toko lain.'],
                ],
                'descriptions' => self::rawMaterialDescriptions(),
            ],
            'raw-material-stock' => [
                'label' => 'Stok Bahan Baku',
                'model' => RawMaterialStockMovementModel::class,
                'roles' => ['admin', 'seller'],
                'image_fields' => [],
                'headers' => ['id', 'store_name', 'raw_material_code', 'raw_material_name', 'movement_type', 'quantity_delta', 'balance_after', 'unit_cost', 'reference_type', 'reference_number', 'notes', 'occurred_at'],
                'examples' => self::rawMaterialStockExamples(),
                'guides' => [
                    ['Tidak membuat master', 'Import stok bahan baku hanya menerima kode bahan yang sudah tersedia. Kode yang tidak ditemukan akan masuk file error.'],
                    ['Restock', 'Isi quantity_delta positif dan unit_cost pembelian. Sistem menghitung average cost tertimbang.'],
                    ['Pemakaian', 'Isi quantity_delta negatif. Sistem tidak mengubah average cost karena tidak ada pembelian baru.'],
                    ['Dampak HPP', 'Jika restock mengubah average cost, HPP semua produk terkait dihitung ulang dan histori dampaknya dicatat.'],
                ],
                'descriptions' => self::rawMaterialStockDescriptions(),
                'bulk_delete_enabled' => false,
            ],
            'product-costing' => [
                'label' => 'HPP & Harga Jual',
                'model' => ProductCostingModel::class,
                'roles' => ['admin', 'seller'],
                'image_fields' => [],
                'headers' => ['id', 'store_name', 'product_id', 'product_name', 'materials', 'labor_cost', 'overhead_cost', 'other_cost', 'hpp', 'margin_percent', 'suggested_price', 'selling_price', 'apply_to_variants'],
                'examples' => self::productCostingExamples(),
                'guides' => [
                    ['Resep bahan', 'Isi materials dengan format KODE:QTY dan pisahkan beberapa bahan menggunakan |, contoh RM-BOX:1|RM-LABEL:2.'],
                    ['Biaya bahan realtime', 'Harga bahan pada HPP selalu diambil dari average_cost master bahan baku saat import dijalankan. Kolom unit cost tidak dicampur ke template HPP.'],
                    ['Perhitungan', 'HPP = biaya bahan + tenaga kerja + overhead + biaya lain. suggested_price dihitung dari HPP dan margin.'],
                    ['Harga jual', 'selling_price dapat diisi manual. apply_to_variants=1 menerapkan harga jual ke seluruh variant produk.'],
                    ['Stok', 'Import HPP tidak menambah atau mengurangi stok produk maupun bahan baku. Produksi dilakukan melalui Stock / Restock Produk.'],
                ],
                'descriptions' => self::productCostingDescriptions(),
                'bulk_delete_enabled' => false,
            ],
            'customer' => [
                'label' => 'Pelanggan',
                'model' => User::class,
                'roles' => ['admin', 'seller'],
                'image_fields' => [],
                'headers' => ['id', 'name', 'email', 'orders_count', 'total_spent', 'last_order_at', 'is_active', 'registered_at'],
                'examples' => [],
                'guides' => [['Export pelanggan', 'Pelanggan merupakan hasil transaksi riil buyer dengan toko. Modul ini export-only agar import tidak membuat relasi transaksi palsu atau customer yang tidak pernah berbelanja.']],
                'descriptions' => self::customerDescriptions(),
                'import_enabled' => false,
                'bulk_delete_enabled' => false,
            ],
            'review' => [
                'label' => 'Review & Rating',
                'model' => ProductReviewModel::class,
                'roles' => ['admin', 'seller'],
                'image_fields' => [],
                'headers' => ['id', 'product_name', 'order_number', 'buyer_name', 'buyer_email', 'rating', 'review', 'is_active', 'created_at'],
                'examples' => [],
                'guides' => [
                    ['Export saja', 'Review berasal dari Buyer dan transaksi selesai. Seller dapat mengekspor data untuk analisis tetapi tidak mengimport review palsu.'],
                ],
                'descriptions' => [],
                'import_enabled' => false,
                'bulk_delete_enabled' => false,
            ],
            'cost-impact' => [
                'label' => 'Laporan Dampak HPP',
                'model' => ProductCostingImpactModel::class,
                'roles' => ['admin', 'seller'],
                'image_fields' => [],
                'headers' => ['id', 'product_name', 'raw_material_code', 'raw_material_name', 'old_average_cost', 'new_average_cost', 'old_hpp', 'new_hpp', 'hpp_change_amount', 'hpp_change_percent', 'old_suggested_price', 'new_suggested_price', 'direction', 'reference_number', 'occurred_at'],
                'examples' => [],
                'guides' => [['Audit biaya', 'Laporan ini dibentuk otomatis saat average cost bahan baku berubah dan memengaruhi HPP produk. Data bersifat audit sehingga hanya dapat diexport.']],
                'descriptions' => self::costImpactDescriptions(),
                'import_enabled' => false,
                'bulk_delete_enabled' => false,
            ],
            'category' => [
                'label' => 'Category',
                'model' => CategoryModel::class,
                'roles' => ['admin'],
                'image_fields' => ['image_url', 'icon_url'],
                'headers' => ['id', 'catalog_group_name', 'parent_category_name', 'name', 'slug', 'image_url', 'icon_url', 'sort_order', 'is_active', 'is_visible_in_menu'],
                'examples' => self::categoryExamples(),
                'guides' => [
                    ['Category Level 1', 'Kosongkan parent_category_name. Isi catalog_group_name dan name.'],
                    ['Category Level 2–3', 'Isi parent_category_name dengan nama parent pada Catalog Group yang sama. Maksimum Level 3.'],
                    ['Relasi belum tersedia', 'Catalog Group yang belum ada dapat dibuat setelah konfirmasi pada tab Antrean. Parent Category harus dapat ditemukan pada group yang sama.'],
                    ['Update dan duplikasi', 'Isi ID Category sendiri untuk update. Nama dicocokkan case-insensitive serta mengabaikan spasi berlebih agar tidak membuat duplikasi.'],
                    ['Gambar', 'image_url dan icon_url mendukung URL, path storage, atau gambar embedded pada cell/baris.'],
                ],
                'descriptions' => self::categoryDescriptions(),
            ],
            'catalog-group' => [
                'label' => 'Catalog Group',
                'model' => CatalogGroupModel::class,
                'roles' => ['admin'],
                'image_fields' => [],
                'headers' => ['id', 'name', 'slug', 'is_active'],
                'examples' => self::catalogGroupExamples(),
                'guides' => [
                    ['Create', 'Isi name. Slug boleh kosong agar dibuat otomatis.'],
                    ['Update', 'Isi id dengan ID Catalog Group sendiri. Jangan memakai ID relasi lain.'],
                    ['Pencegahan duplikasi', 'Nama dicocokkan case-insensitive dan mengabaikan spasi berlebih, tetapi kapitalisasi nama tampilan tetap dipertahankan.'],
                    ['Visibilitas', 'is_active=0 menyimpan data tanpa menampilkannya pada marketplace.'],
                ],
                'descriptions' => self::catalogGroupDescriptions(),
            ],
            'promotion' => [
                'label' => 'Promotion',
                'model' => PromotionModel::class,
                'roles' => ['admin', 'seller'],
                'image_fields' => ['image_url', 'mobile_image_url'],
                'headers' => ['id', 'store_name', 'name', 'image_url', 'mobile_image_url', 'click_action', 'target_name', 'target_catalog_group_name', 'target_url', 'sort_order', 'is_active', 'approval_status'],
                'examples' => self::promotionExamples(),
                'guides' => [
                    ['Promotion platform', 'Admin dapat mengosongkan store_name. Seller otomatis memakai tokonya dan tidak membuat Promotion platform.'],
                    ['Tanpa tujuan klik', 'Gunakan click_action=none dan kosongkan target_name serta target_url.'],
                    ['Tujuan Product', 'Gunakan click_action=product dan isi target_name dengan nama Product yang sudah tersedia. Product tidak dibuat otomatis dari Promotion.'],
                    ['Tujuan Category', 'Gunakan click_action=category dan isi target_name. Jika Category belum ada, isi target_catalog_group_name agar dapat dikonfirmasi melalui Antrean.'],
                    ['Tujuan URL', 'Gunakan click_action=url dan isi target_url dengan URL valid.'],
                    ['Gambar', 'image_url wajib untuk desktop. mobile_image_url optional. Keduanya mendukung URL/path/gambar embedded.'],
                ],
                'descriptions' => self::promotionDescriptions(),
            ],
            'voucher' => [
                'label' => 'Voucher',
                'model' => Voucher::class,
                'roles' => ['admin', 'seller'],
                'image_fields' => ['image'],
                'headers' => ['id', 'store_name', 'voucher_scope', 'code', 'name', 'image', 'discount_target', 'discount_type', 'discount_value', 'min_spend', 'max_discount', 'starts_at', 'ends_at', 'usage_limit', 'is_active'],
                'examples' => self::voucherExamples(),
                'guides' => [
                    ['Voucher platform', 'Admin memakai voucher_scope=platform dan boleh mengosongkan store_name.'],
                    ['Voucher toko', 'Gunakan voucher_scope=store. Admin mengisi store_name; Seller otomatis memakai toko sesi.'],
                    ['Persentase', 'discount_type=percentage. Isi discount_value sebagai persen dan isi max_discount jika ingin membatasi nominal diskon.'],
                    ['Nominal tetap', 'discount_type=fixed. discount_value berisi nominal rupiah dan max_discount boleh kosong.'],
                    ['Periode', 'starts_at harus lebih kecil atau sama dengan ends_at. Format tanggal dapat memakai YYYY-MM-DD atau YYYY-MM-DD HH:MM:SS.'],
                    ['Batas pemakaian', 'usage_limit=0 berarti tanpa batas. Nilai lebih dari 0 menjadi batas total penggunaan.'],
                ],
                'descriptions' => self::voucherDescriptions(),
            ],
            'banner' => [
                'label' => 'Banner',
                'model' => BannerModel::class,
                'roles' => ['admin', 'seller'],
                'image_fields' => ['image_url'],
                'headers' => ['id', 'store_name', 'name', 'image_url', 'sort_order', 'is_active'],
                'examples' => self::bannerExamples(),
                'guides' => [
                    ['Banner Admin', 'Admin wajib mengisi store_name agar Banner terhubung ke toko yang benar.'],
                    ['Banner Seller', 'Seller boleh mengosongkan store_name karena toko diambil otomatis dari sesi.'],
                    ['Gambar', 'image_url wajib dan mendukung URL, path storage, atau gambar embedded pada cell/baris.'],
                    ['Urutan', 'sort_order lebih kecil ditampilkan lebih awal.'],
                    ['Update', 'Isi id dengan ID Banner sendiri. Nama toko tetap divalidasi agar tidak mengubah kepemilikan secara salah.'],
                ],
                'descriptions' => self::bannerDescriptions(),
            ],
        ];
    }

    private static function productExamples(): array
    {
        $simpleAutoSku = [
            'id' => '', 'store_name' => 'Toko Nusantara', 'catalog_group_name' => 'Kebutuhan Harian', 'name' => 'Kecap Manis Premium 600 ml', 'slug' => '', 'description' => 'Kecap manis botol 600 ml untuk kebutuhan keluarga', 'brand' => 'Rasa Kita', 'primary_category_name' => 'Kecap', 'category_names' => 'Kecap, Bumbu Masak', 'status' => 'published', 'is_active' => '1', 'thumbnail' => 'https://example.com/kecap-600.jpg', 'image_url' => 'https://example.com/kecap-600-detail.jpg', 'image_alt' => 'Kecap Manis Premium 600 ml', 'sku' => '', 'variant_name' => 'Default', 'price' => '25000', 'is_default' => '1',
        ];

        return [
            self::example($simpleAutoSku, 'Product Baru Sederhana', 'Toko menjual satu Product dengan satu harga dan tidak memiliki pilihan warna atau ukuran. Stok tidak dibentuk dari import Product.', 'Pilih Import Data Baru. Kosongkan id dan sku, isi variant_name=Default, price, serta is_default=1. Saldo stok diisi terpisah melalui import Stok Produk.', 'Product baru dan default variant dibuat. Backend membentuk SKU unik otomatis.', 'Ini pola yang disarankan untuk barang biasa yang hanya memiliki satu harga.'),
            self::example([
                ...$simpleAutoSku, 'name' => 'Kecap Manis Refill 500 ml', 'description' => 'Kecap manis kemasan refill', 'thumbnail' => 'https://example.com/kecap-refill.jpg', 'image_url' => '', 'image_alt' => 'Kecap Manis Refill 500 ml', 'sku' => 'KECAP-REFILL-500', 'price' => '18000',
            ], 'Product Baru dengan SKU Manual', 'Toko mempunyai kode SKU internal sendiri untuk Product sederhana.', 'Pilih Import Data Baru, kosongkan id, lalu isi SKU manual yang belum pernah digunakan di toko tersebut.', 'Product dibuat menggunakan SKU KECAP-REFILL-500.', 'SKU manual harus unik dalam file dan unik pada toko yang sama.'),
            self::exampleRows([
                ['id' => '', 'store_name' => 'Toko Nusantara', 'catalog_group_name' => 'Fashion', 'name' => 'Kaos Polos Premium', 'slug' => '', 'description' => 'Kaos katun combed 30s', 'brand' => 'Basic Wear', 'primary_category_name' => 'Kaos', 'category_names' => 'Kaos, Pakaian', 'status' => 'published', 'is_active' => '1', 'thumbnail' => 'https://example.com/kaos.jpg', 'image_url' => '', 'image_alt' => 'Kaos Polos Premium', 'sku' => '', 'variant_name' => 'Hitam - M', 'price' => '85000', 'is_default' => '1'],
                ['id' => '', 'store_name' => 'Toko Nusantara', 'catalog_group_name' => 'Fashion', 'name' => 'Kaos Polos Premium', 'slug' => '', 'description' => 'Kaos katun combed 30s', 'brand' => 'Basic Wear', 'primary_category_name' => 'Kaos', 'category_names' => 'Kaos, Pakaian', 'status' => 'published', 'is_active' => '1', 'thumbnail' => 'https://example.com/kaos.jpg', 'image_url' => '', 'image_alt' => 'Kaos Polos Premium', 'sku' => '', 'variant_name' => 'Hitam - L', 'price' => '85000', 'is_default' => '0'],
                ['id' => '', 'store_name' => 'Toko Nusantara', 'catalog_group_name' => 'Fashion', 'name' => 'Kaos Polos Premium', 'slug' => '', 'description' => 'Kaos katun combed 30s', 'brand' => 'Basic Wear', 'primary_category_name' => 'Kaos', 'category_names' => 'Kaos, Pakaian', 'status' => 'published', 'is_active' => '1', 'thumbnail' => 'https://example.com/kaos.jpg', 'image_url' => '', 'image_alt' => 'Kaos Polos Premium', 'sku' => '', 'variant_name' => 'Putih - M', 'price' => '82000', 'is_default' => '0'],
            ], 'Product Baru Multi-Variant', 'Satu Product Kaos memiliki beberapa pilihan warna dan ukuran.', 'Pilih Import Data Baru. Gunakan beberapa baris dengan store_name dan name yang sama, ulangi data utama, lalu bedakan variant_name. SKU boleh kosong dan hanya satu baris memakai is_default=1.', 'Satu Product dibuat dengan tiga variant dan tiga SKU unik otomatis.', 'Jangan mengubah nama Product pada baris variant berikutnya. Variant name dalam Product yang sama tidak boleh kembar.'),
            self::example([
                'id' => '', 'store_name' => 'Toko Nusantara', 'catalog_group_name' => 'Informasi', 'name' => 'Layanan Pesanan Khusus', 'slug' => '', 'description' => 'Halaman informasi untuk pesanan yang memerlukan konsultasi', 'brand' => '', 'primary_category_name' => 'Informasi', 'category_names' => 'Informasi', 'status' => 'draft', 'is_active' => '1', 'thumbnail' => '', 'image_url' => '', 'image_alt' => '', 'sku' => '', 'variant_name' => '', 'price' => '', 'is_default' => '',
            ], 'Product Tanpa Variant', 'Admin ingin menyiapkan Product informasi yang belum mempunyai harga.', 'Pilih Import Data Baru dan kosongkan seluruh kolom variant: sku, variant_name, price, serta is_default.', 'Hanya Product draft yang dibuat tanpa record variant.', 'Product belum dapat dibeli sampai variant dan harga tersedia; saldo stok kemudian dikelola melalui Persediaan.'),
            self::example([
                'id' => '', 'store_name' => 'Toko Nusantara', 'catalog_group_name' => 'Makanan', 'name' => 'Sambal Bawang 200 ml', 'slug' => '', 'description' => 'Sambal bawang rumahan', 'brand' => 'Dapur Ibu', 'primary_category_name' => 'Sambal', 'category_names' => 'Sambal, Bumbu Masak', 'status' => 'published', 'is_active' => '1', 'thumbnail' => 'https://example.com/sambal-thumb.webp', 'image_url' => 'products/sambal-detail.webp', 'image_alt' => 'Sambal Bawang 200 ml', 'sku' => '', 'variant_name' => 'Default', 'price' => '22000', 'is_default' => '1',
            ], 'Product dengan Gambar', 'Product memakai gambar utama dari URL dan gambar detail dari storage atau gambar yang ditempel ke cell.', 'Isi thumbnail/image_url dengan URL atau path. Gambar juga dapat ditempel tepat pada cell dan baris terkait.', 'Gambar disimpan atau dinormalisasi ke storage Laravel, lalu Product dibuat dengan SKU otomatis.', 'Gunakan gambar JPG, JPEG, PNG, atau WEBP dengan ukuran yang wajar.'),
            self::example([
                'id' => '', 'store_name' => 'Toko Nusantara', 'catalog_group_name' => 'Kuliner Tradisional', 'name' => 'Tape Singkong Manis', 'slug' => '', 'description' => 'Tape singkong segar', 'brand' => 'Dapur Desa', 'primary_category_name' => 'Tape', 'category_names' => 'Tape, Makanan Fermentasi', 'status' => 'draft', 'is_active' => '1', 'thumbnail' => '', 'image_url' => '', 'image_alt' => '', 'sku' => '', 'variant_name' => '500 gram', 'price' => '15000', 'is_default' => '1',
            ], 'Product dengan Relasi Baru', 'Catalog Group Kuliner Tradisional dan Category Tape belum tersedia saat Product diimport.', 'Pilih Import Data Baru dan isi nama relasi yang diinginkan. Jalankan validasi import.', 'Permintaan pembuatan Catalog Group dan Category masuk ke tab Antrean untuk dipilih Lanjutkan atau Batal.', 'Relasi aman dapat dibuat otomatis setelah disetujui; Toko tidak dibuat otomatis.'),
            self::example([
                'id' => '', 'store_name' => '  toko nusantara ', 'catalog_group_name' => ' makanan ', 'name' => 'Keripik Singkong Balado', 'slug' => '', 'description' => 'Keripik singkong balado', 'brand' => 'Cemilan Kita', 'primary_category_name' => ' keripik ', 'category_names' => ' Keripik , Cemilan , Makanan Pedas ', 'status' => 'PUBLISHED', 'is_active' => 'YA', 'thumbnail' => '', 'image_url' => '', 'image_alt' => '', 'sku' => '', 'variant_name' => 'Default', 'price' => '12000', 'is_default' => 'YA',
            ], 'Product Banyak Category', 'Product mempunyai Category utama dan beberapa Category tambahan, sementara kapital dan spasi data sumber tidak konsisten.', 'Pisahkan category_names dengan koma. Sistem membersihkan spasi serta mencocokkan nama tanpa peka kapital.', 'Relasi lama dipakai tanpa membuat duplikasi dan SKU dibentuk otomatis.', 'Kapitalisasi nama yang sudah tersimpan di database tetap dipertahankan.'),
            self::example([
                ...$simpleAutoSku, 'id' => '109', 'name' => 'Kecap Manis Premium 600 ml', 'description' => 'Deskripsi dan harga diperbarui', 'sku' => 'KECAP-600', 'price' => '27000',
            ], 'Update Product dan Variant', 'Product ID 109 dan variant dengan SKU KECAP-600 sudah tersedia dan perlu diperbarui.', 'Pilih Import Update Data, isi id Product, gunakan SKU lama untuk menunjuk variant, lalu isi nilai terbaru.', 'Product ID 109 dan variant yang sesuai diperbarui tanpa membuat Product baru.', 'Mode Update mewajibkan id. SKU milik Product lain akan ditolak.'),
            self::exampleRows([
                ['id' => '109', 'store_name' => 'Toko Nusantara', 'catalog_group_name' => 'Kebutuhan Harian', 'name' => 'Kecap Manis Premium 600 ml', 'slug' => '', 'description' => 'Menambah ukuran baru', 'brand' => 'Rasa Kita', 'primary_category_name' => 'Kecap', 'category_names' => 'Kecap, Bumbu Masak', 'status' => 'published', 'is_active' => '1', 'thumbnail' => 'https://example.com/kecap-600.jpg', 'image_url' => '', 'image_alt' => 'Kecap Manis Premium', 'sku' => 'KECAP-600', 'variant_name' => '600 ml', 'price' => '27000', 'is_default' => '1'],
                ['id' => '109', 'store_name' => 'Toko Nusantara', 'catalog_group_name' => 'Kebutuhan Harian', 'name' => 'Kecap Manis Premium 600 ml', 'slug' => '', 'description' => 'Menambah ukuran baru', 'brand' => 'Rasa Kita', 'primary_category_name' => 'Kecap', 'category_names' => 'Kecap, Bumbu Masak', 'status' => 'published', 'is_active' => '1', 'thumbnail' => 'https://example.com/kecap-600.jpg', 'image_url' => '', 'image_alt' => 'Kecap Manis Premium', 'sku' => '', 'variant_name' => '1 Liter', 'price' => '42000', 'is_default' => '0'],
            ], 'Update Product dan Tambah Variant', 'Product lama tetap diperbarui sekaligus mendapat variant ukuran 1 Liter yang belum tersedia.', 'Pilih Import Update Data dan isi id yang sama pada kedua baris. Gunakan SKU lama untuk variant lama; kosongkan SKU pada variant baru agar backend membuatnya otomatis.', 'Variant lama diperbarui dan variant 1 Liter ditambahkan dengan SKU baru tanpa mengganti default.', 'Variant baru tetap berada pada Product ID yang sama.'),
            self::exampleRows([
                ['id' => '', 'store_name' => 'Toko Nusantara', 'catalog_group_name' => 'Makanan', 'name' => 'Madu Hutan 250 ml', 'slug' => '', 'description' => 'Madu hutan', 'brand' => 'Alam', 'primary_category_name' => 'Madu', 'category_names' => 'Madu', 'status' => 'published', 'is_active' => '1', 'thumbnail' => '', 'image_url' => '', 'image_alt' => '', 'sku' => 'MADU-250', 'variant_name' => '250 ml', 'price' => '65000', 'is_default' => '1'],
                ['id' => '', 'store_name' => 'Toko Nusantara', 'catalog_group_name' => 'Makanan', 'name' => 'Madu Hutan 500 ml', 'slug' => '', 'description' => 'Madu hutan', 'brand' => 'Alam', 'primary_category_name' => 'Madu', 'category_names' => 'Madu', 'status' => 'published', 'is_active' => '1', 'thumbnail' => '', 'image_url' => '', 'image_alt' => '', 'sku' => 'MADU-250', 'variant_name' => '500 ml', 'price' => '110000', 'is_default' => '1'],
            ], 'Validasi SKU Kembar', 'Dua baris Product berbeda memakai SKU manual yang sama dalam satu file.', 'Import file pada mode Data Baru untuk menguji validasi SKU.', 'Preview menolak file dan menjelaskan baris SKU yang kembar. Tidak ada data yang disimpan.', 'Ubah salah satu SKU atau kosongkan SKU agar backend membuat SKU unik otomatis.'),
        ];
    }

    private static function productDescriptions(): array
    {
        return [
            ['id', 'Kondisional', 'Angka bulat', '109', 'Wajib pada mode Import Update Data dan harus kosong pada mode Import Data Baru.', 'Jangan isi dengan ID Store, Category, Catalog Group, atau Variant.'],
            ['store_name', 'Wajib Admin, otomatis Seller', 'Nama toko', 'Toko Nusantara', 'Dicocokkan setelah trim dan secara case-insensitive. Seller memakai toko dari sesi.', 'Saat update melalui Admin, tetap isi nama toko pemilik Product.'],
            ['catalog_group_name', 'Wajib untuk menentukan/membuat Category', 'Nama Catalog Group', 'Kebutuhan Harian', 'Dicocokkan berdasarkan nama. Jika belum ada, perlu konfirmasi Antrean sebelum dibuat.', 'Tidak menggunakan catalog_group_id.'],
            ['name', 'Wajib', 'Teks maksimal 255', 'Kecap Manis Premium', 'Nama dibersihkan dari spasi berlebih tetapi kapitalisasi dipertahankan.', 'Baris multi-variant harus memakai nama Product yang sama persis secara makna.'],
            ['slug', 'Optional', 'slug', 'kecap-manis-premium', 'Dibuat otomatis dari name jika kosong.', 'Pastikan unik pada ruang Product yang berlaku.'],
            ['description', 'Optional', 'Teks panjang', 'Kecap botol 600 ml', 'Boleh kosong.', 'Ulangi pada semua baris multi-variant agar tidak tertimpa kosong.'],
            ['brand', 'Optional', 'Teks', 'Rasa Kita', 'Boleh kosong dan kapitalisasi dipertahankan.', 'Ulangi pada semua baris multi-variant.'],
            ['primary_category_name', 'Wajib', 'Nama Category', 'Kecap', 'Category utama dicocokkan berdasarkan nama dan Catalog Group.', 'Tidak menggunakan primary_category_id.'],
            ['category_names', 'Optional', 'Daftar nama dipisahkan koma', 'Kecap, Bumbu Masak', 'Setiap nama dicocokkan. Relasi yang belum ada meminta konfirmasi sebelum dibuat.', 'primary_category_name sebaiknya juga dicantumkan dalam daftar untuk keterbacaan, tetapi sistem akan menambahkannya otomatis.'],
            ['status', 'Optional', 'draft|published|archived', 'published', 'Default draft. Nilai tidak peka kapital.', 'Marketplace hanya menampilkan Product published yang memenuhi aturan publik lainnya.'],
            ['is_active', 'Optional', '1|0|true|false|ya|tidak', '1', 'Default aktif.', 'status=published tetapi is_active=0 tetap tidak tampil.'],
            ['thumbnail', 'Optional', 'URL/path/gambar cell', 'https://example.com/kecap.jpg', 'Mendukung URL, path storage, atau gambar embedded.', 'Gambar disimpan ke storage Laravel saat import.'],
            ['image_url', 'Optional', 'URL/path/gambar cell', 'products/kecap-detail.jpg', 'Menjadi gambar galeri utama jika diisi.', 'Boleh ditempel sebagai gambar embedded pada cell/baris.'],
            ['image_alt', 'Optional', 'Teks', 'Kecap Manis Premium', 'Alt text gambar.', 'Jika kosong, nama Product digunakan sebagai fallback.'],
            ['sku', 'Optional', 'Teks unik per toko', 'KECAP-600', 'Jika kosong, backend membentuk SKU unik otomatis. SKU manual yang kembar di file atau sudah dipakai Product lain akan ditolak.', 'Pada mode Update, SKU lama memperbarui variant terkait; SKU kosong pada variant baru akan menghasilkan SKU otomatis.'],
            ['variant_name', 'Wajib saat membuat variant', 'Teks', 'Default atau Hitam - M', 'Jika variant dibuat dan nama kosong, sistem menggunakan Default.', 'Untuk barang tanpa pilihan, gunakan nama Default.'],
            ['price', 'Wajib saat membuat variant', 'Angka ≥ 0', '25000', 'Tidak memakai pemisah ribuan atau simbol Rp.', 'Harga berada pada variant, bukan Product utama.'],
                        ['is_default', 'Wajib untuk multi-variant, optional untuk variant pertama', '1|0|ya|tidak', '1', 'Hanya satu variant per Product boleh bernilai aktif sebagai default.', 'Jika kosong pada variant pertama, sistem menjadikannya default. Variant berikutnya yang kosong tidak mengganti default lama.'],
        ];
    }

    private static function categoryExamples(): array
    {
        return [
            self::example(['id' => '', 'catalog_group_name' => 'Makanan', 'parent_category_name' => '', 'name' => 'Makanan Ringan', 'slug' => '', 'image_url' => '', 'icon_url' => '', 'sort_order' => '1', 'is_active' => '1', 'is_visible_in_menu' => '1'], 'Level 1', 'Membuat Category Level 1.', 'Kosongkan parent_category_name dan isi Catalog Group.', 'Category dibuat langsung di bawah Catalog Group Makanan.', 'Level dihitung otomatis.'),
            self::example(['id' => '', 'catalog_group_name' => 'Makanan', 'parent_category_name' => 'Makanan Ringan', 'name' => 'Keripik', 'slug' => '', 'image_url' => '', 'icon_url' => '', 'sort_order' => '1', 'is_active' => '1', 'is_visible_in_menu' => '1'], 'Level 2', 'Membuat Category Level 2.', 'Isi parent_category_name dengan nama Level 1 pada group yang sama.', 'Parent ditemukan dan Category Level 2 dibuat.', 'Nama parent harus unik dalam Catalog Group.'),
            self::example(['id' => '', 'catalog_group_name' => 'Makanan', 'parent_category_name' => 'Keripik', 'name' => 'Keripik Singkong', 'slug' => '', 'image_url' => 'https://example.com/keripik.jpg', 'icon_url' => '', 'sort_order' => '1', 'is_active' => '1', 'is_visible_in_menu' => '1'], 'Level 3 + Gambar', 'Membuat Category Level 3 dengan gambar.', 'Isi parent Level 2 dan URL/path pada image_url.', 'Full slug, level, dan gambar diproses otomatis.', 'Level 3 adalah batas maksimum.'),
            self::example(['id' => '', 'catalog_group_name' => 'Kuliner Tradisional', 'parent_category_name' => '', 'name' => 'Tape', 'slug' => '', 'image_url' => '', 'icon_url' => '', 'sort_order' => '2', 'is_active' => '1', 'is_visible_in_menu' => '1'], 'Relasi Baru', 'Catalog Group belum tersedia.', 'Isi nama Catalog Group baru dan jalankan preview import.', 'Masuk Antrean dan dibuat setelah Lanjutkan.', 'Batal menghentikan proses tanpa menyimpan.', 'Pilih Lanjutkan atau Batal melalui Antrean.'),
            self::example(['id' => '', 'catalog_group_name' => ' makanan ', 'parent_category_name' => ' makanan ringan ', 'name' => '  KERIPIK  ', 'slug' => '', 'image_url' => '', 'icon_url' => '', 'sort_order' => '3', 'is_active' => 'YA', 'is_visible_in_menu' => 'YA'], 'Normalisasi', 'Kapital dan spasi berbeda dari data lama.', 'Isi nama sebagaimana data sumber; sistem melakukan normalisasi pencocokan.', 'Data lama ditemukan dan tidak terjadi duplikasi.', 'Kapitalisasi nama database tidak diubah.'),
            self::example(['id' => '15', 'catalog_group_name' => 'Makanan', 'parent_category_name' => 'Makanan Ringan', 'name' => 'Keripik dan Kerupuk', 'slug' => '', 'image_url' => '', 'icon_url' => '', 'sort_order' => '5', 'is_active' => '1', 'is_visible_in_menu' => '1'], 'Update', 'Update Category berdasarkan ID sendiri.', 'Isi ID Category, group, parent, dan data terbaru.', 'Category ID 15 diperbarui.', 'Jangan isi ID relasi.'),
            self::example(['id' => '', 'catalog_group_name' => 'Fashion', 'parent_category_name' => '', 'name' => 'Pakaian Arsip', 'slug' => '', 'image_url' => '', 'icon_url' => '', 'sort_order' => '99', 'is_active' => '0', 'is_visible_in_menu' => '0'], 'Nonaktif', 'Category nonaktif dan disembunyikan dari menu.', 'Isi kedua flag dengan 0.', 'Data tersimpan tetapi tidak tampil ke Buyer.', 'Data tetap dapat dikelola Admin.'),
            self::example(['id' => '', 'catalog_group_name' => 'Elektronik', 'parent_category_name' => '', 'name' => 'Aksesori Komputer', 'slug' => 'aksesori-komputer', 'image_url' => 'https://example.com/accessory.webp', 'icon_url' => 'https://example.com/accessory-icon.png', 'sort_order' => '4', 'is_active' => '1', 'is_visible_in_menu' => '1'], 'Gambar', 'Gambar dan ikon berasal dari URL.', 'Isi image_url dan icon_url atau tempel gambar pada cell terkait.', 'File disimpan ke storage Laravel.', 'Gunakan format gambar yang didukung.'),
            self::example(['id' => '', 'catalog_group_name' => 'Makanan', 'parent_category_name' => '', 'name' => 'Makanan Ringan', 'slug' => '', 'image_url' => '', 'icon_url' => '', 'sort_order' => '7', 'is_active' => '1', 'is_visible_in_menu' => '1'], 'Duplikasi', 'Nama sama dengan Category yang sudah ada.', 'Gunakan nama/group/parent yang sama.', 'Data lama dicocokkan, bukan membuat duplikat.', 'Gunakan ID bila ingin update eksplisit.'),
            self::example(['id' => '', 'catalog_group_name' => 'Makanan', 'parent_category_name' => 'Keripik Singkong', 'name' => 'Pedas Ekstra', 'slug' => '', 'image_url' => '', 'icon_url' => '', 'sort_order' => '1', 'is_active' => '1', 'is_visible_in_menu' => '1'], 'Validasi Gagal', 'Mencoba membuat Level 4.', 'Isi parent yang sudah berada pada Level 3.', 'Baris ditolak dan masuk file error.', 'Perbaiki hierarki maksimal sampai Level 3.', 'Unduh file error lalu perbaiki parent.'),
        ];
    }

    private static function categoryDescriptions(): array
    {
        return [
            ['id', 'Kondisional', 'Angka bulat', '15', 'Wajib pada mode Import Update Data dan harus kosong pada mode Import Data Baru.', 'Jangan isi dengan ID Catalog Group atau parent.'],
            ['catalog_group_name', 'Wajib', 'Nama Catalog Group', 'Makanan', 'Dicocokkan berdasarkan nama. Group baru perlu konfirmasi Antrean.', 'Tidak menggunakan catalog_group_id.'],
            ['parent_category_name', 'Optional', 'Nama Category', 'Makanan Ringan', 'Parent harus berada pada Catalog Group yang sama. Kosong berarti Level 1.', 'Maksimum Level 3.'],
            ['name', 'Wajib', 'Teks maksimal 255', 'Keripik', 'Nama dibersihkan dan dicocokkan case-insensitive.', 'Kapitalisasi tampilan dipertahankan.'],
            ['slug', 'Optional', 'slug', 'keripik', 'Dibuat otomatis jika kosong.', 'Harus aman digunakan pada URL.'],
            ['image_url', 'Optional', 'URL/path/gambar cell', 'https://example.com/keripik.jpg', 'Mendukung URL, path, dan gambar embedded.', 'Gambar kartu Category.'],
            ['icon_url', 'Optional', 'URL/path/gambar cell', 'categories/keripik-icon.png', 'Mendukung URL, path, dan gambar embedded.', 'Ikon menu Category.'],
            ['sort_order', 'Optional', 'Angka bulat', '1', 'Default 0.', 'Nilai kecil tampil lebih awal.'],
            ['is_active', 'Optional', '1|0|ya|tidak', '1', 'Default aktif.', 'Nonaktif tidak tampil kepada Buyer.'],
            ['is_visible_in_menu', 'Optional', '1|0|ya|tidak', '1', 'Default tampil.', 'Bisa aktif tetapi disembunyikan dari menu.'],
        ];
    }

    private static function catalogGroupExamples(): array
    {
        return [
            self::example(['id' => '', 'name' => 'Kebutuhan Harian', 'slug' => '', 'is_active' => '1'], 'Create Otomatis', 'Membuat group baru dengan slug otomatis.', 'Isi name dan kosongkan slug.', 'Slug dibuat menjadi kebutuhan-harian.', 'Gunakan cara ini bila tidak membutuhkan slug khusus.'),
            self::example(['id' => '', 'name' => 'Makanan', 'slug' => 'makanan', 'is_active' => '1'], 'Create Manual', 'Membuat group dengan slug manual.', 'Isi name dan slug.', 'Nama dan slug disimpan.', 'Slug harus unik.'),
            self::example(['id' => '', 'name' => 'Minuman', 'slug' => '', 'is_active' => '1'], 'Aktif', 'Group aktif.', 'Isi is_active=1.', 'Tampil di marketplace.', 'Category aktif di dalamnya tetap mengikuti filter masing-masing.'),
            self::example(['id' => '', 'name' => 'Fashion', 'slug' => '', 'is_active' => '0'], 'Nonaktif', 'Group nonaktif.', 'Isi is_active=0.', 'Tersimpan tetapi tidak tampil di marketplace.', 'Data turunan tidak dihapus.'),
            self::example(['id' => '5', 'name' => 'Elektronik dan Gadget', 'slug' => '', 'is_active' => '1'], 'Update', 'Update berdasarkan ID sendiri.', 'Isi ID Catalog Group dan data terbaru.', 'Catalog Group ID 5 diperbarui.', 'Jangan memakai ID Category.'),
            self::example(['id' => '', 'name' => ' kebutuhan harian ', 'slug' => '', 'is_active' => 'YA'], 'Normalisasi', 'Spasi dan kapital berbeda.', 'Masukkan nama sumber apa adanya.', 'Dicocokkan dengan group yang ada dan tidak duplikat.', 'Nama database tidak dipaksa lowercase.'),
            self::example(['id' => '', 'name' => 'Ibu & Bayi', 'slug' => '', 'is_active' => '1'], 'Karakter Khusus', 'Nama mengandung simbol.', 'Kosongkan slug agar dibuat otomatis.', 'Slug aman dibuat otomatis.', 'Nama tampilan tetap Ibu & Bayi.'),
            self::example(['id' => '', 'name' => 'Kecantikan dan Perawatan', 'slug' => '', 'is_active' => '1'], 'Nama Panjang', 'Nama cukup panjang.', 'Isi name normal.', 'Nama tetap dipertahankan.', 'Batas maksimal mengikuti validasi model.'),
            self::example(['id' => '', 'name' => 'Olahraga', 'slug' => 'sport', 'is_active' => '1'], 'Slug Kustom', 'Slug berbeda dari nama.', 'Isi slug yang diinginkan.', 'Slug manual digunakan jika tidak konflik.', 'Gunakan huruf kecil dan tanda hubung.'),
            self::example(['id' => '', 'name' => '', 'slug' => '', 'is_active' => '1'], 'Validasi Gagal', 'Nama kosong.', 'Biarkan name kosong untuk menguji file error.', 'Baris ditolak dan masuk file error.', 'Name wajib diisi.', 'Perbaiki name lalu import ulang.'),
        ];
    }

    private static function catalogGroupDescriptions(): array
    {
        return [
            ['id', 'Kondisional', 'Angka bulat', '5', 'Wajib pada mode Import Update Data dan harus kosong pada mode Import Data Baru.', 'Data lama tidak akan diperbarui pada mode Data Baru.'],
            ['name', 'Wajib', 'Teks maksimal 255', 'Kebutuhan Harian', 'Nama dicocokkan case-insensitive dan spasi berlebih dibersihkan.', 'Kapitalisasi tampilan dipertahankan.'],
            ['slug', 'Optional', 'slug', 'kebutuhan-harian', 'Dibuat otomatis jika kosong dan dijaga unik.', 'Boleh berbeda dari name.'],
            ['is_active', 'Optional', '1|0|ya|tidak', '1', 'Default aktif.', 'Nonaktif tidak tampil di marketplace.'],
        ];
    }

    private static function promotionExamples(): array
    {
        $base = ['id' => '', 'store_name' => '', 'name' => '', 'image_url' => 'https://example.com/promo.jpg', 'mobile_image_url' => '', 'click_action' => 'none', 'target_name' => '', 'target_catalog_group_name' => '', 'target_url' => '', 'sort_order' => '1', 'is_active' => '1', 'approval_status' => 'pending'];

        return [
            self::example([...$base, 'name' => 'Promo Platform'], 'Platform', 'Promotion platform tanpa target.', 'Admin mengosongkan store_name dan memakai click_action=none.', 'Promotion dibuat untuk platform.', 'Seller tidak dapat membuat Promotion platform.'),
            self::example([...$base, 'store_name' => 'Toko Nusantara', 'name' => 'Promo Toko'], 'Toko', 'Promotion milik toko.', 'Isi store_name untuk Admin. Seller boleh kosong.', 'Toko dicocokkan berdasarkan nama.', 'Seller memakai toko sesi.'),
            self::example([...$base, 'name' => 'Promo Kecap', 'click_action' => 'product', 'target_name' => 'Kecap Manis Premium'], 'Target Product', 'Target Product berdasarkan nama.', 'Gunakan click_action=product dan isi target_name.', 'target_id diisi otomatis dari Product yang ditemukan.', 'Product harus sudah tersedia.'),
            self::example([...$base, 'name' => 'Promo Tape', 'click_action' => 'category', 'target_name' => 'Tape', 'target_catalog_group_name' => 'Kuliner Tradisional'], 'Target Category Baru', 'Target Category belum ada.', 'Isi nama Category dan Catalog Group lalu jalankan preview.', 'Antrean meminta konfirmasi sebelum Category dibuat.', 'Pilih Lanjutkan atau Batal.', 'Konfirmasi melalui tab Antrean.'),
            self::example([...$base, 'name' => 'Promo URL', 'click_action' => 'url', 'target_url' => 'https://example.com/promo'], 'Target URL', 'Target URL eksternal.', 'Gunakan click_action=url dan isi target_url.', 'URL divalidasi dan disimpan.', 'Kosongkan target_name.'),
            self::example([...$base, 'name' => 'Promo Mobile', 'mobile_image_url' => 'https://example.com/promo-mobile.jpg'], 'Responsive Image', 'Gambar desktop dan mobile.', 'Isi image_url dan mobile_image_url.', 'Kedua gambar disimpan.', 'mobile_image_url optional.'),
            self::example([...$base, 'id' => '8', 'name' => 'Promo Update'], 'Update', 'Update berdasarkan ID sendiri.', 'Isi ID Promotion dan data terbaru.', 'Promotion ID 8 diperbarui.', 'ID target tetap tidak ditulis manual.'),
            self::example([...$base, 'name' => 'Promo Nonaktif', 'is_active' => '0'], 'Nonaktif', 'Promotion nonaktif.', 'Isi is_active=0.', 'Tidak tampil di marketplace.', 'Data tetap tersimpan.'),
            self::example([...$base, 'name' => 'Promo Approved', 'approval_status' => 'approved'], 'Approval', 'Admin mengimpor Promotion approved.', 'Admin dapat mengisi approved; Seller tetap pending.', 'Status sesuai role diterapkan.', 'Seller tidak dapat memaksa approved.'),
            self::example([...$base, 'name' => 'Promo Target Hilang', 'click_action' => 'product', 'target_name' => 'Produk Tidak Ada'], 'Validasi Gagal', 'Target Product tidak ditemukan.', 'Gunakan nama Product yang belum tersedia.', 'Baris diblokir dan masuk file error.', 'Product tidak dibuat otomatis dari Promotion.', 'Buat Product dahulu lalu import ulang.'),
        ];
    }

    private static function promotionDescriptions(): array
    {
        return [
            ['id', 'Kondisional', 'Angka bulat', '8', 'Wajib pada mode Import Update Data dan harus kosong pada mode Import Data Baru.', 'Tidak menggunakan ID target.'],
            ['store_name', 'Optional Admin, otomatis Seller', 'Nama toko', 'Toko Nusantara', 'Kosong pada Admin berarti platform. Seller memakai toko sesi.', 'Nama dicocokkan case-insensitive.'],
            ['name', 'Wajib', 'Teks', 'Promo Kecap', 'Nama Promotion wajib.', 'Kapitalisasi dipertahankan.'],
            ['image_url', 'Wajib', 'URL/path/gambar cell', 'https://example.com/promo.jpg', 'Gambar desktop wajib dan dapat disimpan ke storage.', 'Boleh embedded.'],
            ['mobile_image_url', 'Optional', 'URL/path/gambar cell', 'https://example.com/promo-mobile.jpg', 'Gambar khusus layar kecil.', 'Jika kosong dapat memakai image_url.'],
            ['click_action', 'Wajib', 'none|product|category|url', 'product', 'Menentukan jenis target.', 'Field target lain harus sesuai aksi.'],
            ['target_name', 'Kondisional', 'Nama Product/Category', 'Kecap Manis Premium', 'Wajib untuk action product/category.', 'Tidak menggunakan target_id.'],
            ['target_catalog_group_name', 'Kondisional', 'Nama Catalog Group', 'Kuliner Tradisional', 'Diperlukan untuk memperjelas/membuat Category target.', 'Relasi baru perlu konfirmasi Antrean.'],
            ['target_url', 'Kondisional', 'URL valid', 'https://example.com/promo', 'Wajib untuk action=url.', 'Kosongkan untuk action selain url.'],
            ['sort_order', 'Optional', 'Angka bulat', '1', 'Default 0.', 'Nilai kecil tampil lebih awal.'],
            ['is_active', 'Optional', '1|0|ya|tidak', '1', 'Default aktif.', 'Nonaktif tidak tampil.'],
            ['approval_status', 'Admin', 'pending|approved|rejected', 'approved', 'Seller selalu pending.', 'Marketplace umumnya hanya menampilkan approved aktif.'],
        ];
    }

    private static function voucherExamples(): array
    {
        $base = ['id' => '', 'store_name' => '', 'voucher_scope' => 'platform', 'code' => '', 'name' => '', 'image' => '', 'discount_target' => 'product', 'discount_type' => 'percentage', 'discount_value' => '10', 'min_spend' => '50000', 'max_discount' => '20000', 'starts_at' => '2026-08-01 00:00:00', 'ends_at' => '2026-08-31 23:59:59', 'usage_limit' => '100', 'is_active' => '1'];

        return [
            self::example([...$base, 'code' => 'HEMAT10', 'name' => 'Hemat 10 Persen'], 'Platform Percentage', 'Voucher platform persentase.', 'Admin memakai scope platform dan mengosongkan store_name.', 'Voucher platform dibuat.', 'max_discount membatasi diskon maksimum.'),
            self::example([...$base, 'store_name' => 'Toko Nusantara', 'voucher_scope' => 'store', 'code' => 'TOKO20', 'name' => 'Diskon Toko'], 'Voucher Toko', 'Voucher milik toko.', 'Gunakan scope store. Admin isi store_name; Seller boleh kosong.', 'Toko dicocokkan berdasarkan nama.', 'Seller memakai toko sesi.'),
            self::example([...$base, 'code' => 'POTONG25K', 'name' => 'Potongan 25 Ribu', 'discount_type' => 'fixed', 'discount_value' => '25000', 'max_discount' => ''], 'Fixed Discount', 'Diskon nominal tetap.', 'Gunakan discount_type=fixed dan isi nominal.', 'max_discount boleh kosong.', 'Jangan menulis Rp atau pemisah ribuan.'),
            self::example([...$base, 'code' => 'ONGKIR', 'name' => 'Diskon Ongkir', 'discount_target' => 'shipping'], 'Shipping', 'Diskon pengiriman.', 'Gunakan discount_target=shipping.', 'Target shipping disimpan.', 'Perhitungan tetap mengikuti aturan checkout.'),
            self::example([...$base, 'code' => 'GAMBAR', 'name' => 'Voucher Bergambar', 'image' => 'https://example.com/voucher.png'], 'Gambar', 'Voucher memakai gambar URL atau embedded.', 'Isi image atau tempel gambar pada cell.', 'Gambar dipindah ke storage.', 'Kolom image optional.'),
            self::example([...$base, 'id' => '10', 'code' => 'UPDATE10', 'name' => 'Voucher Update'], 'Update', 'Update berdasarkan ID sendiri.', 'Isi ID Voucher dan data terbaru.', 'Voucher ID 10 diperbarui.', 'Code tetap divalidasi unik.'),
            self::example([...$base, 'code' => 'NONAKTIF', 'name' => 'Voucher Nonaktif', 'is_active' => '0'], 'Nonaktif', 'Voucher nonaktif.', 'Isi is_active=0.', 'Tidak tersedia bagi Buyer.', 'Data tidak dihapus.'),
            self::example([...$base, 'code' => 'UNLIMITED', 'name' => 'Tanpa Batas', 'usage_limit' => '0'], 'Unlimited', 'Pemakaian tanpa batas.', 'Isi usage_limit=0.', 'Voucher tidak dibatasi jumlah penggunaan total.', 'Batas per-user tetap mengikuti implementasi aplikasi.'),
            self::example([...$base, 'code' => 'TANGGAL', 'name' => 'Tanggal Excel', 'starts_at' => '2026-09-01', 'ends_at' => '2026-09-30'], 'Tanggal', 'Tanggal tanpa jam.', 'Gunakan YYYY-MM-DD atau format lengkap.', 'Tanggal dinormalisasi ke waktu database.', 'Pastikan zona waktu aplikasi konsisten.'),
            self::example([...$base, 'code' => 'SALAH', 'name' => 'Tanggal Salah', 'starts_at' => '2026-09-30', 'ends_at' => '2026-09-01'], 'Validasi Gagal', 'Tanggal akhir lebih kecil dari tanggal mulai.', 'Isi periode terbalik untuk menguji validasi.', 'Baris ditolak dan masuk file error.', 'Perbaiki ends_at agar setelah starts_at.', 'Perbaiki tanggal lalu import ulang.'),
        ];
    }

    private static function voucherDescriptions(): array
    {
        return [
            ['id', 'Kondisional', 'Angka bulat', '10', 'Wajib pada mode Import Update Data dan harus kosong pada mode Import Data Baru.', 'Kode Voucher lama tidak akan ditimpa pada mode Data Baru.'],
            ['store_name', 'Wajib untuk scope store pada Admin, otomatis Seller', 'Nama toko', 'Toko Nusantara', 'Seller memakai toko sesi. Platform boleh kosong.', 'Tidak menggunakan store_id.'],
            ['voucher_scope', 'Wajib', 'platform|store', 'store', 'Seller selalu store.', 'Menentukan kepemilikan Voucher.'],
            ['code', 'Wajib', 'Teks unik', 'HEMAT10', 'Kode dinormalisasi sesuai aturan aplikasi dan harus unik.', 'Gunakan kode mudah dibaca.'],
            ['name', 'Wajib', 'Teks', 'Hemat 10 Persen', 'Nama wajib.', 'Ditampilkan kepada Buyer.'],
            ['image', 'Optional', 'URL/path/gambar cell', 'https://example.com/voucher.png', 'Mendukung URL/path/embedded.', 'Disimpan ke storage Laravel.'],
            ['discount_target', 'Wajib', 'product|shipping', 'product', 'Menentukan objek diskon.', 'Harus sesuai perhitungan checkout.'],
            ['discount_type', 'Wajib', 'fixed|percentage', 'percentage', 'Menentukan cara hitung discount_value.', 'fixed=nominal, percentage=persen.'],
            ['discount_value', 'Wajib', 'Angka ≥ 0', '10', 'Tanpa simbol persen atau Rp.', 'Untuk percentage, 10 berarti 10%.'],
            ['min_spend', 'Wajib', 'Angka ≥ 0', '50000', 'Minimum subtotal agar Voucher berlaku.', 'Tanpa pemisah ribuan.'],
            ['max_discount', 'Optional', 'Angka ≥ 0', '20000', 'Umumnya dipakai untuk percentage.', 'Boleh kosong untuk fixed.'],
            ['starts_at', 'Wajib', 'YYYY-MM-DD HH:MM:SS', '2026-08-01 00:00:00', 'Tanggal mulai valid.', 'Format tanggal Excel juga didukung.'],
            ['ends_at', 'Wajib', 'YYYY-MM-DD HH:MM:SS', '2026-08-31 23:59:59', 'Harus sama atau setelah starts_at.', 'Periode terbalik ditolak.'],
            ['usage_limit', 'Wajib', 'Angka bulat ≥ 0', '100', '0 berarti tanpa batas.', 'Nilai positif menjadi batas total.'],
            ['is_active', 'Optional', '1|0|ya|tidak', '1', 'Default aktif.', 'Voucher tetap harus berada dalam periode berlaku.'],
        ];
    }

    private static function bannerExamples(): array
    {
        return [
            self::example(['id' => '', 'store_name' => 'Toko Nusantara', 'name' => 'Banner Utama', 'image_url' => 'https://example.com/banner.jpg', 'sort_order' => '1', 'is_active' => '1'], 'URL Image', 'Banner baru menggunakan URL.', 'Isi store_name, name, image_url, dan urutan.', 'Gambar disimpan ke storage dan Banner aktif dibuat.', 'Admin wajib mengisi store_name.'),
            self::example(['id' => '', 'store_name' => 'Toko Nusantara', 'name' => 'Banner Kedua', 'image_url' => '', 'sort_order' => '2', 'is_active' => '1'], 'Embedded Image', 'Gambar ditempel langsung pada cell image_url.', 'Tempel gambar pada cell image_url di baris ini.', 'Gambar embedded diekstrak.', 'Pastikan gambar berada tepat pada baris data.'),
            self::example(['id' => '3', 'store_name' => 'Toko Nusantara', 'name' => 'Banner Update', 'image_url' => 'https://example.com/banner-new.jpg', 'sort_order' => '1', 'is_active' => '1'], 'Update', 'Update berdasarkan ID sendiri.', 'Isi ID Banner, toko pemilik, dan data terbaru.', 'Banner ID 3 diperbarui.', 'ID harus milik Banner pada toko tersebut.'),
            self::example(['id' => '', 'store_name' => 'Toko Nusantara', 'name' => 'Banner Nonaktif', 'image_url' => 'https://example.com/banner-off.jpg', 'sort_order' => '9', 'is_active' => '0'], 'Nonaktif', 'Banner nonaktif.', 'Isi is_active=0.', 'Tersimpan tetapi tidak tampil di toko.', 'Cocok untuk arsip sementara.'),
            self::example(['id' => '', 'store_name' => ' toko nusantara ', 'name' => 'Banner Spasi', 'image_url' => 'https://example.com/banner-space.jpg', 'sort_order' => '3', 'is_active' => 'YA'], 'Normalisasi', 'Nama toko berbeda kapital/spasi.', 'Isi nama toko dari sumber apa adanya.', 'Toko lama ditemukan tanpa duplikasi.', 'Nama database tidak diubah.'),
            self::example(['id' => '', 'store_name' => 'Toko Cabang Bandung', 'name' => 'Banner Cabang', 'image_url' => 'https://example.com/banner-bandung.jpg', 'sort_order' => '1', 'is_active' => '1'], 'Admin Multi Toko', 'Admin memilih toko lain.', 'Isi store_name sesuai toko tujuan.', 'Banner dikaitkan ke toko berdasarkan nama.', 'Pastikan nama toko unik.'),
            self::example(['id' => '', 'store_name' => '', 'name' => 'Banner Seller', 'image_url' => 'https://example.com/banner-seller.jpg', 'sort_order' => '1', 'is_active' => '1'], 'Seller Auto Store', 'Seller mengosongkan store_name.', 'Seller cukup mengisi data Banner.', 'Toko diambil otomatis dari sesi Seller.', 'Contoh ini tidak berlaku untuk Admin.'),
            self::example(['id' => '', 'store_name' => 'Toko Tidak Ada', 'name' => 'Banner Toko Hilang', 'image_url' => 'https://example.com/banner-missing.jpg', 'sort_order' => '1', 'is_active' => '1'], 'Relasi Gagal', 'Nama toko tidak ditemukan.', 'Gunakan nama toko yang tidak tersedia untuk menguji validasi.', 'Baris diblokir.', 'Toko tidak dibuat otomatis karena membutuhkan onboarding/legal.', 'Buat toko lebih dahulu lalu import ulang.'),
            self::example(['id' => '', 'store_name' => 'Toko Nusantara', 'name' => 'Banner Path', 'image_url' => 'banners/banner-local.webp', 'sort_order' => '4', 'is_active' => '1'], 'Storage Path', 'Gambar menggunakan path storage.', 'Isi path relatif pada disk public.', 'Path dipakai setelah validasi.', 'Pastikan file benar-benar tersedia.'),
            self::example(['id' => '', 'store_name' => 'Toko Nusantara', 'name' => '', 'image_url' => 'https://example.com/banner.jpg', 'sort_order' => '5', 'is_active' => '1'], 'Validasi Gagal', 'Nama Banner kosong.', 'Kosongkan name untuk menguji file error.', 'Baris ditolak dan masuk file error.', 'Name wajib.', 'Perbaiki name lalu import ulang.'),
        ];
    }

    private static function bannerDescriptions(): array
    {
        return [
            ['id', 'Kondisional', 'Angka bulat', '3', 'Wajib pada mode Import Update Data dan harus kosong pada mode Import Data Baru.', 'Banner lama tidak akan ditimpa pada mode Data Baru.'],
            ['store_name', 'Wajib Admin, otomatis Seller', 'Nama toko', 'Toko Nusantara', 'Dicocokkan berdasarkan nama. Seller memakai toko sesi.', 'Toko tidak dibuat otomatis dari import Banner.'],
            ['name', 'Wajib', 'Teks', 'Banner Utama', 'Nama wajib.', 'Digunakan untuk pengelolaan Admin/Seller.'],
            ['image_url', 'Wajib', 'URL/path/gambar cell', 'https://example.com/banner.jpg', 'Mendukung URL, path storage, dan embedded image.', 'Disimpan ke storage Laravel.'],
            ['sort_order', 'Optional', 'Angka bulat', '1', 'Default 0.', 'Nilai kecil tampil lebih awal.'],
            ['is_active', 'Optional', '1|0|ya|tidak', '1', 'Default aktif.', 'Nonaktif tidak tampil pada Store Page.'],
        ];
    }


    private static function financeModule(string $label, string $type): array
    {
        $settlement = in_array($type, ['payable', 'receivable'], true);
        $statusGuide = $settlement
            ? 'Status dihitung dari paid_amount: open jika belum dibayar, partial jika sebagian, paid jika lunas, atau cancelled untuk pembatalan.'
            : 'Status yang tersedia adalah draft, posted, atau cancelled. paid_amount tidak digunakan untuk pemasukan dan pengeluaran.';

        return [
            'label' => $label,
            'model' => FinancialTransactionModel::class,
            'roles' => ['admin', 'seller'],
            'image_fields' => [],
            'fixed_type' => $type,
            'headers' => ['id', 'store_name', 'order_number', 'counterparty_email', 'reference_number', 'title', 'description', 'amount', 'paid_amount', 'status', 'due_date', 'occurred_at', 'settled_at', 'is_active'],
            'examples' => self::financeExamples($label, $type),
            'guides' => [
                ['Jenis transaksi', 'Jenis ditentukan otomatis oleh modul '.$label.' dan tidak perlu ditulis pada file.'],
                ['Mode Data Baru', 'Kolom id wajib kosong. reference_number boleh kosong agar dibuat otomatis.'],
                ['Mode Update Data', 'Isi id transaksi. Data tetap dibatasi ke toko Seller yang sedang aktif.'],
                ['Relasi Order', 'Isi order_number bila transaksi terkait pesanan. Nomor harus tersedia dan terkait toko yang sama untuk Seller.'],
                ['Status dan pembayaran', $statusGuide],
            ],
            'descriptions' => self::financeDescriptions($label, $type),
        ];
    }

    private static function financeExamples(string $label, string $type): array
    {
        $settlement = in_array($type, ['payable', 'receivable'], true);
        $base = [
            'id' => '',
            'store_name' => 'Toko Nusantara',
            'order_number' => '',
            'counterparty_email' => '',
            'reference_number' => strtoupper(substr($type, 0, 3)).'-0001',
            'title' => $label.' Operasional',
            'description' => 'Contoh transaksi '.$label,
            'amount' => '1500000',
            'paid_amount' => '0',
            'status' => $settlement ? 'open' : 'posted',
            'due_date' => $settlement ? '2026-08-31' : '',
            'occurred_at' => '2026-08-04 10:00:00',
            'settled_at' => '',
            'is_active' => '1',
        ];

        $examples = [
            self::example($base, 'Create Data', $label.' baru tanpa order.', 'Isi toko, judul, nominal, status, dan tanggal.', $label.' baru dibuat.'),
            self::example([...$base, 'reference_number' => '', 'title' => $label.' Nomor Otomatis'], 'Nomor Otomatis', 'Reference number dikosongkan.', 'Kosongkan reference_number.', 'Nomor unik dibuat backend.'),
            self::example([...$base, 'order_number' => 'ORD-202608-0001', 'title' => $label.' Terkait Order'], 'Relasi Order', 'Transaksi terkait pesanan.', 'Isi order_number yang tersedia.', 'Relasi order tersimpan.'),
            self::example([...$base, 'counterparty_email' => 'buyer@example.com'], 'Relasi User', 'Transaksi memiliki pihak terkait.', 'Isi email user aktif.', 'counterparty tersimpan.'),
        ];

        if ($settlement) {
            $examples[] = self::example([...$base, 'status' => 'partial', 'paid_amount' => '500000'], 'Pembayaran Sebagian', 'Sebagian nominal telah dibayar.', 'Isi paid_amount lebih kecil dari amount.', 'Status dihitung menjadi partial dan saldo tersisa.');
            $examples[] = self::example([...$base, 'status' => 'paid', 'paid_amount' => '1500000', 'settled_at' => '2026-08-05 12:00:00'], 'Lunas', 'Nominal telah dibayar penuh.', 'Samakan paid_amount dengan amount dan isi settled_at.', 'Status dihitung menjadi paid.');
            $examples[] = self::example([...$base, 'status' => 'cancelled'], 'Dibatalkan', 'Hutang atau piutang dibatalkan.', 'Gunakan status cancelled.', 'Transaksi tidak dihitung sebagai outstanding.');
        } else {
            $examples[] = self::example([...$base, 'status' => 'draft', 'title' => $label.' Draft'], 'Draft', 'Transaksi belum diposting.', 'Gunakan status draft.', 'Transaksi tersimpan sebagai draft.');
            $examples[] = self::example([...$base, 'status' => 'posted', 'title' => $label.' Diposting'], 'Diposting', 'Transaksi sudah diakui.', 'Gunakan status posted.', 'Transaksi aktif dalam laporan.');
            $examples[] = self::example([...$base, 'status' => 'cancelled', 'title' => $label.' Dibatalkan'], 'Dibatalkan', 'Transaksi dibatalkan tanpa dihapus.', 'Gunakan status cancelled.', 'Transaksi tetap memiliki audit trail.');
        }

        $examples[] = self::example([...$base, 'is_active' => '0', 'title' => $label.' Nonaktif'], 'Nonaktif', 'Transaksi disimpan sebagai arsip.', 'Isi is_active=0.', 'Data tidak aktif tetapi tidak dihapus.');
        $examples[] = self::example([...$base, 'id' => '10', 'title' => $label.' Update'], 'Update Data', 'Transaksi lama diperbarui.', 'Gunakan mode update dan isi id.', 'Data ID 10 diperbarui.');
        $examples[] = self::example([...$base, 'amount' => '100000', 'paid_amount' => '200000'], 'Validasi Gagal', 'Pembayaran melebihi nominal.', 'Isi paid_amount lebih besar dari amount untuk pengujian.', 'Baris ditolak dan masuk file error.', 'paid_amount tidak boleh melebihi amount.');

        return $examples;
    }

    private static function financeDescriptions(string $label, string $type): array
    {
        $settlement = in_array($type, ['payable', 'receivable'], true);
        $statusOptions = $settlement ? 'open|partial|paid|cancelled' : 'draft|posted|cancelled';
        $statusExample = $settlement ? 'open' : 'posted';
        $paidNote = $settlement ? 'Tidak boleh lebih besar dari amount.' : 'Isi 0 untuk pemasukan atau pengeluaran.';
        $dueNote = $settlement ? 'Tanggal jatuh tempo hutang atau piutang.' : 'Boleh kosong untuk pemasukan atau pengeluaran.';

        return [
            ['id', 'Kondisional', 'Angka bulat', '10', 'Wajib pada mode update dan kosong pada mode data baru.', 'ID '.$label.' sendiri.'],
            ['store_name', 'Wajib Admin, otomatis Seller', 'Nama toko', 'Toko Nusantara', 'Dicocokkan berdasarkan nama.', 'Seller selalu memakai toko sesi.'],
            ['order_number', 'Optional', 'Nomor pesanan', 'ORD-202608-0001', 'Harus tersedia bila diisi.', 'Seller hanya dapat memakai order tokonya.'],
            ['counterparty_email', 'Optional', 'Email user', 'buyer@example.com', 'Harus merupakan user aktif.', 'Relasi pihak transaksi.'],
            ['reference_number', 'Optional', 'Teks unik', 'FIN-0001', 'Kosong berarti dibuat otomatis.', 'Unik per transaksi.'],
            ['title', 'Wajib', 'Teks', $label.' Operasional', 'Maksimum 160 karakter.', 'Judul transaksi.'],
            ['description', 'Optional', 'Teks', 'Keterangan transaksi', 'Boleh kosong.', 'Catatan internal.'],
            ['amount', 'Wajib', 'Angka positif', '1500000', 'Tanpa simbol Rp.', 'Nilai transaksi.'],
            ['paid_amount', $settlement ? 'Optional' : 'Tidak digunakan', 'Angka ≥ 0', $settlement ? '500000' : '0', $paidNote, 'Jumlah telah dibayar.'],
            ['status', 'Wajib', $statusOptions, $statusExample, 'Harus sesuai pilihan modul.', 'Status transaksi.'],
            ['due_date', 'Optional', 'YYYY-MM-DD', $settlement ? '2026-08-31' : '', 'Tanggal valid.', $dueNote],
            ['occurred_at', 'Wajib', 'Tanggal dan waktu', '2026-08-04 10:00:00', 'Format tanggal Excel didukung.', 'Waktu transaksi.'],
            ['settled_at', $settlement ? 'Optional' : 'Tidak digunakan', 'Tanggal dan waktu', $settlement ? '2026-08-05 12:00:00' : '', 'Isi saat hutang atau piutang lunas.', 'Waktu penyelesaian.'],
            ['is_active', 'Optional', '1|0|ya|tidak', '1', 'Default aktif.', 'Soft delete tetap terpisah.'],
        ];
    }

    private static function orderExamples(): array
    {
        $base = ['id' => '', 'order_number' => 'ORD-202608-0001', 'buyer_email' => 'buyer@example.com', 'store_name' => 'Toko Nusantara', 'sub_order_number' => 'SUB-202608-0001', 'order_type' => 'normal', 'status' => 'pending', 'payment_status' => 'pending', 'payment_method' => 'bank_transfer', 'shipping_address' => 'Jl. Merdeka No. 1, Jakarta', 'sku' => 'PRODUK-001', 'product_name' => 'Produk Contoh', 'variant_name' => 'Default', 'quantity' => '2', 'price' => '75000', 'shipping_cost' => '15000', 'courier' => 'JNE', 'service' => 'REG', 'destination_id' => '501', 'tracking_number' => '', 'preorder_release_at' => '', 'booking_expires_at' => '', 'received_at' => ''];

        return [
            self::example($base, 'Order Normal', 'Membuat pesanan normal satu item.', 'Isi buyer, toko, SKU, quantity, harga, dan pengiriman.', 'Order, sub-order, dan item dibuat.'),
            self::exampleRows([$base, [...$base, 'sku' => 'PRODUK-002', 'product_name' => 'Produk Kedua', 'quantity' => '1', 'price' => '125000']], 'Multi Item', 'Satu pesanan memiliki dua SKU.', 'Ulangi order_number dan sub_order_number pada dua baris.', 'Dua item masuk ke sub-order yang sama.'),
            self::example([...$base, 'order_number' => 'ORD-PRE-0001', 'sub_order_number' => 'SUB-PRE-0001', 'order_type' => 'preorder', 'preorder_release_at' => '2026-09-01 09:00:00'], 'Preorder', 'Membuat pesanan preorder.', 'Gunakan order_type=preorder dan isi preorder_release_at.', 'Tanggal rilis preorder tersimpan.'),
            self::example([...$base, 'order_number' => 'ORD-BOOK-0001', 'sub_order_number' => 'SUB-BOOK-0001', 'order_type' => 'booking', 'booking_expires_at' => '2026-08-10 23:59:59'], 'Booking', 'Membuat pesanan booking.', 'Gunakan order_type=booking dan isi booking_expires_at.', 'Batas booking tersimpan.'),
            self::example([...$base, 'status' => 'processing', 'payment_status' => 'paid'], 'Sudah Dibayar', 'Pesanan telah dibayar dan sedang diproses.', 'Gunakan status processing dan payment_status paid.', 'Order tampil sebagai dibayar dan diproses.'),
            self::example([...$base, 'status' => 'shipped', 'payment_status' => 'paid', 'tracking_number' => 'JNE123456789'], 'Dikirim', 'Pesanan sudah dikirim.', 'Isi tracking_number dan status shipped.', 'Nomor resi tersimpan.'),
            self::example([...$base, 'status' => 'received', 'payment_status' => 'paid', 'received_at' => '2026-08-08 14:00:00'], 'Diterima', 'Buyer telah menerima pesanan.', 'Isi status received dan received_at.', 'Pesanan dapat direview.'),
            self::example([...$base, 'id' => '25', 'status' => 'processing'], 'Update Order', 'Memperbarui pesanan yang ada.', 'Pilih mode update dan isi id Order.', 'Order ID 25 diperbarui.'),
            self::example([...$base, 'store_name' => ''], 'Seller Auto Store', 'Seller mengosongkan store_name.', 'Seller menggunakan template dengan toko kosong.', 'Toko diambil dari sesi Seller.'),
            self::example([...$base, 'sku' => 'SKU-TIDAK-ADA'], 'Validasi Gagal', 'SKU tidak ditemukan pada toko.', 'Isi SKU yang tidak tersedia.', 'Baris ditolak dan masuk file error.', 'Product tidak dibuat otomatis dari import order.'),
        ];
    }

    private static function orderDescriptions(): array
    {
        return [
            ['id', 'Kondisional', 'Angka bulat', '25', 'Wajib mode update dan kosong mode data baru.', 'ID Order, bukan Sub Order.'],
            ['order_number', 'Wajib', 'Teks unik', 'ORD-202608-0001', 'Boleh diulang untuk beberapa item dalam file yang sama.', 'Nomor pesanan.'],
            ['buyer_email', 'Wajib', 'Email', 'buyer@example.com', 'User harus tersedia dan aktif.', 'Pemilik pesanan.'],
            ['store_name', 'Wajib Admin, otomatis Seller', 'Nama toko', 'Toko Nusantara', 'Dicocokkan berdasarkan nama.', 'Menentukan Sub Order.'],
            ['sub_order_number', 'Optional', 'Teks unik', 'SUB-202608-0001', 'Kosong berarti dibuat otomatis.', 'Nomor pesanan per toko.'],
            ['order_type', 'Wajib', 'normal|preorder|booking', 'normal', 'Harus sesuai pilihan.', 'Jenis pesanan.'],
            ['status', 'Wajib', 'pending|processing|shipped|received|completed|cancelled', 'pending', 'Harus sesuai lifecycle order.', 'Status global order.'],
            ['payment_status', 'Wajib', 'unpaid|pending|paid|failed|refunded', 'pending', 'Harus sesuai pilihan.', 'Status pembayaran.'],
            ['payment_method', 'Optional', 'Teks', 'bank_transfer', 'Maksimum 80 karakter.', 'Metode bayar.'],
            ['shipping_address', 'Wajib', 'Teks', 'Jl. Merdeka No. 1', 'Alamat lengkap.', 'Alamat pengiriman order.'],
            ['sku', 'Wajib', 'SKU variant', 'PRODUK-001', 'Harus milik toko terkait.', 'Menentukan produk dan variant.'],
            ['product_name', 'Optional', 'Teks', 'Produk Contoh', 'Diisi otomatis dari SKU bila kosong.', 'Snapshot nama produk.'],
            ['variant_name', 'Optional', 'Teks', 'Default', 'Diisi otomatis dari SKU bila kosong.', 'Informasi variant.'],
            ['quantity', 'Wajib', 'Angka bulat positif', '2', 'Minimal 1.', 'Jumlah item.'],
            ['price', 'Optional', 'Angka ≥ 0', '75000', 'Kosong memakai harga variant.', 'Harga per item.'],
            ['shipping_cost', 'Optional', 'Angka ≥ 0', '15000', 'Per sub-order.', 'Ongkir.'],
            ['courier', 'Optional', 'Teks', 'JNE', 'Per sub-order.', 'Kurir.'],
            ['service', 'Optional', 'Teks', 'REG', 'Per sub-order.', 'Layanan kurir.'],
            ['destination_id', 'Optional', 'Teks', '501', 'Sesuai integrasi ongkir.', 'Tujuan pengiriman.'],
            ['tracking_number', 'Optional', 'Teks', 'JNE123456789', 'Isi ketika dikirim.', 'Nomor resi.'],
            ['preorder_release_at', 'Kondisional', 'Tanggal dan waktu', '2026-09-01 09:00:00', 'Wajib untuk preorder.', 'Tanggal rilis.'],
            ['booking_expires_at', 'Kondisional', 'Tanggal dan waktu', '2026-08-10 23:59:59', 'Wajib untuk booking.', 'Batas booking.'],
            ['received_at', 'Kondisional', 'Tanggal dan waktu', '2026-08-08 14:00:00', 'Isi untuk status received/completed.', 'Waktu diterima buyer.'],
        ];
    }

    private static function stockExamples(): array
    {
        $base = ['id' => '', 'store_name' => 'Toko Nusantara', 'sku' => 'PRODUK-001', 'product_name' => 'Produk Contoh', 'variant_name' => 'Default', 'movement_type' => 'adjustment', 'quantity_delta' => '10', 'balance_after' => '', 'reference_type' => 'manual_import', 'reference_id' => '', 'order_number' => '', 'notes' => 'Penyesuaian stok awal', 'occurred_at' => '2026-08-04 10:00:00'];

        return [
            self::example([...$base, 'movement_type' => 'in', 'quantity_delta' => '20', 'notes' => 'Barang masuk gudang'], 'Barang Masuk', 'Menambah stok variant.', 'Isi movement_type=in dan delta positif.', 'Stok bertambah 20.'),
            self::example([...$base, 'movement_type' => 'out', 'quantity_delta' => '-3', 'notes' => 'Barang rusak'], 'Barang Keluar', 'Mengurangi stok variant.', 'Isi movement_type=out dan delta negatif.', 'Stok berkurang 3.'),
            self::example($base, 'Adjustment', 'Penyesuaian stok manual.', 'Isi delta positif atau negatif.', 'Movement adjustment dibuat.'),
            self::example([...$base, 'order_number' => 'ORD-202608-0001', 'reference_type' => 'order'], 'Relasi Order', 'Movement terkait pesanan.', 'Isi order_number yang tersedia.', 'Order tersimpan pada movement.'),
            self::example([...$base, 'reference_type' => 'purchase', 'reference_id' => 'PO-0001'], 'Referensi Eksternal', 'Movement terkait pembelian.', 'Isi reference_type dan reference_id.', 'Referensi audit tersimpan.'),
            self::example([...$base, 'store_name' => ''], 'Seller Auto Store', 'Seller mengosongkan toko.', 'Seller cukup mengisi SKU dan perubahan.', 'Toko diambil dari sesi Seller.'),
            self::example([...$base, 'occurred_at' => '2026-08-04'], 'Tanggal Excel', 'Tanggal tanpa jam.', 'Gunakan tanggal valid.', 'Tanggal dinormalisasi.'),
            self::example([...$base, 'id' => '40', 'balance_after' => '100', 'quantity_delta' => ''], 'Update Menjadi Saldo', 'Mode update menetapkan target saldo terbaru.', 'Isi id movement lama dan balance_after target.', 'Sistem membuat movement koreksi sampai stok 100.'),
            self::example([...$base, 'sku' => 'SKU-TIDAK-ADA'], 'Validasi Gagal', 'SKU tidak ditemukan.', 'Gunakan SKU tidak tersedia.', 'Baris ditolak.', 'Produk harus dibuat lebih dahulu.'),
            self::example([...$base, 'movement_type' => 'out', 'quantity_delta' => '-999999'], 'Stok Negatif', 'Pengurangan melebihi stok.', 'Isi delta sangat besar.', 'Baris ditolak dan stok tidak berubah.', 'Stok akhir tidak boleh negatif.'),
        ];
    }

    private static function stockDescriptions(): array
    {
        return [
            ['id', 'Kondisional', 'Angka bulat', '40', 'Wajib pada mode update.', 'History lama tidak diedit; ID menentukan movement referensi.'],
            ['store_name', 'Wajib Admin, otomatis Seller', 'Nama toko', 'Toko Nusantara', 'Dicocokkan berdasarkan nama.', 'Seller selalu toko sesi.'],
            ['sku', 'Wajib', 'SKU variant', 'PRODUK-001', 'Harus tersedia pada toko.', 'Kunci utama import stok.'],
            ['product_name', 'Optional', 'Teks', 'Produk Contoh', 'Digunakan untuk pemeriksaan tambahan.', 'Tidak membuat Product baru.'],
            ['variant_name', 'Optional', 'Teks', 'Default', 'Digunakan untuk informasi.', 'SKU tetap menjadi pencocokan.'],
            ['movement_type', 'Wajib', 'in|out|adjustment|reservation|release', 'adjustment', 'Harus sesuai arah perubahan. Delta positif selain release dianggap penambahan stok produksi dan memakai resep bahan bila tersedia.', 'release hanya untuk pengembalian barang jadi dan tidak memakai bahan baku.'],
            ['quantity_delta', 'Kondisional', 'Angka bulat bukan 0', '10', 'Wajib create. Positif menambah, negatif mengurangi.', 'Tidak boleh membuat stok negatif.'],
            ['balance_after', 'Kondisional', 'Angka bulat ≥ 0', '100', 'Dapat dipakai sebagai target pada mode update.', 'Sistem menghitung delta koreksi.'],
            ['reference_type', 'Optional', 'Teks', 'manual_import', 'Default manual_import.', 'Jenis sumber movement.'],
            ['reference_id', 'Optional', 'Teks', 'PO-0001', 'Maksimum 120 karakter.', 'ID sumber eksternal.'],
            ['order_number', 'Optional', 'Nomor pesanan', 'ORD-202608-0001', 'Harus tersedia bila diisi.', 'Relasi order.'],
            ['notes', 'Optional', 'Teks', 'Penyesuaian stok', 'Maksimum 1000 karakter.', 'Alasan perubahan.'],
            ['occurred_at', 'Wajib', 'Tanggal dan waktu', '2026-08-04 10:00:00', 'Format Excel didukung.', 'Waktu movement.'],
        ];
    }

    private static function rawMaterialExamples(): array
    {
        $base = ['id' => '', 'store_name' => 'Toko Nusantara', 'code' => 'RM-BOX', 'name' => 'Box Kemasan', 'unit' => 'pcs', 'minimum_stock' => '20', 'average_cost' => '2500', 'is_active' => '1'];
        return [
            self::example($base, 'Master Baru', 'Membuat bahan baku baru tanpa menyentuh stok.', 'Pilih Import Data Baru dan kosongkan id.', 'Master bahan baku dibuat.'),
            self::example([...$base, 'code' => 'RM-LABEL', 'name' => 'Label Produk', 'unit' => 'lembar'], 'Satuan Lain', 'Membuat bahan dengan satuan lembar.', 'Isi unit sesuai penggunaan.', 'Satuan tersimpan.'),
            self::example([...$base, 'minimum_stock' => '100'], 'Minimum Stok', 'Menentukan batas stok minimum.', 'Isi minimum_stock 100.', 'Batas minimum tersimpan.'),
            self::example([...$base, 'average_cost' => '2750'], 'Biaya Awal', 'Menetapkan biaya rata-rata awal.', 'Isi average_cost tanpa stok.', 'HPP terkait akan mengikuti biaya saat resep dibuat.'),
            self::example([...$base, 'is_active' => '0'], 'Nonaktif', 'Menyimpan bahan nonaktif.', 'Isi is_active=0.', 'Bahan tidak dapat dipilih untuk resep baru.'),
            self::example([...$base, 'store_name' => ''], 'Seller Auto Store', 'Seller tidak perlu mengisi toko.', 'Kosongkan store_name.', 'Toko diambil dari sesi.'),
            self::example([...$base, 'id' => '10', 'name' => 'Box Premium'], 'Update Master', 'Memperbarui master bahan baku.', 'Pilih mode update dan isi id.', 'Data master diperbarui.'),
            self::example([...$base, 'id' => '10', 'average_cost' => '3000'], 'Biaya Tidak Diubah dari Master', 'Mencegah perubahan average cost tanpa histori pembelian.', 'Pilih mode update lalu ubah average_cost.', 'Baris ditolak dan diarahkan melakukan restock melalui modul Stok Bahan Baku.'),
            self::example([...$base, 'code' => ''], 'Kode Kosong', 'Validasi kode wajib.', 'Kosongkan code.', 'Baris ditolak.'),
            self::example([...$base, 'code' => 'RM-BOX'], 'Kode Duplikat', 'Mencegah kode ganda pada toko.', 'Import sebagai data baru dengan kode yang sudah ada.', 'Baris ditolak dan diarahkan memakai mode update.'),
        ];
    }

    private static function rawMaterialDescriptions(): array
    {
        return [
            ['id', 'Kondisional', 'Angka bulat', '10', 'Wajib mode update, kosong mode data baru.', 'ID master bahan baku.'],
            ['store_name', 'Wajib Admin, otomatis Seller', 'Nama toko', 'Toko Nusantara', 'Harus tersedia.', 'Tidak membuat toko baru.'],
            ['code', 'Wajib', 'Teks unik per toko', 'RM-BOX', 'Maksimum 100 karakter.', 'Kunci pencarian stok bahan baku.'],
            ['name', 'Wajib', 'Teks', 'Box Kemasan', 'Maksimum 255 karakter.', 'Nama bahan.'],
            ['unit', 'Wajib', 'Teks', 'pcs', 'Maksimum 50 karakter.', 'Satuan konsumsi resep.'],
            ['minimum_stock', 'Optional', 'Angka ≥ 0', '20', 'Tidak boleh negatif.', 'Batas minimum.'],
            ['average_cost', 'Data baru saja', 'Angka ≥ 0', '2500', 'Biaya awal bahan. Pada mode update nilainya tidak boleh diubah.', 'Perubahan biaya setelah bahan dibuat dilakukan melalui import Stok Bahan Baku / restock agar weighted average dan histori HPP tercatat.'],
            ['is_active', 'Optional', '0|1', '1', 'Default aktif.', 'Bahan aktif dapat dipakai resep.'],
        ];
    }

    private static function rawMaterialStockExamples(): array
    {
        $base = ['id' => '', 'store_name' => 'Toko Nusantara', 'raw_material_code' => 'RM-BOX', 'raw_material_name' => 'Box Kemasan', 'movement_type' => 'restock', 'quantity_delta' => '100', 'balance_after' => '', 'unit_cost' => '3000', 'reference_type' => 'purchase', 'reference_number' => 'PO-001', 'notes' => 'Restock bahan', 'occurred_at' => '2026-08-15 10:00:00'];
        return [
            self::example($base, 'Restock', 'Menambah stok bahan baku dengan harga beli baru.', 'Isi delta positif dan unit_cost.', 'Stok dan average cost diperbarui.'),
            self::example([...$base, 'quantity_delta' => '-5', 'unit_cost' => '', 'movement_type' => 'usage'], 'Pemakaian', 'Mengurangi stok bahan baku manual.', 'Isi delta negatif.', 'Stok berkurang tanpa mengubah average cost.'),
            self::example([...$base, 'reference_number' => 'INV-SUP-1001'], 'Referensi', 'Mencatat nomor dokumen pembelian.', 'Isi reference_number.', 'Audit movement lebih mudah.'),
            self::example([...$base, 'store_name' => ''], 'Seller Auto Store', 'Seller memakai toko sesi.', 'Kosongkan store_name.', 'Toko seller digunakan.'),
            self::example([...$base, 'unit_cost' => '4500'], 'Harga Naik', 'Harga pembelian naik.', 'Restock dengan unit_cost lebih tinggi.', 'Average cost dan HPP terkait diperbarui serta laporan dampak dibuat.'),
            self::example([...$base, 'unit_cost' => '1800'], 'Harga Turun', 'Harga pembelian turun.', 'Restock dengan unit_cost lebih rendah.', 'Average cost dan HPP ikut turun.'),
            self::example([...$base, 'id' => '40', 'balance_after' => '250', 'quantity_delta' => ''], 'Target Saldo', 'Mode update menyesuaikan ke saldo target.', 'Isi id dan balance_after.', 'Sistem membuat movement koreksi.'),
            self::example([...$base, 'raw_material_code' => 'RM-TIDAK-ADA'], 'Master Tidak Ada', 'Stok tidak boleh membuat master baru.', 'Isi kode yang tidak tersedia.', 'Baris ditolak.'),
            self::example([...$base, 'quantity_delta' => '-999999'], 'Stok Negatif', 'Pengeluaran melebihi saldo.', 'Isi delta negatif besar.', 'Baris ditolak.'),
            self::example([...$base, 'quantity_delta' => '0'], 'Delta Nol', 'Movement harus mengubah saldo.', 'Isi delta 0.', 'Baris ditolak.'),
        ];
    }

    private static function rawMaterialStockDescriptions(): array
    {
        return [
            ['id', 'Kondisional', 'Angka bulat', '40', 'Wajib mode update.', 'ID movement referensi.'],
            ['store_name', 'Wajib Admin, otomatis Seller', 'Nama toko', 'Toko Nusantara', 'Harus tersedia.', 'Tidak membuat toko.'],
            ['raw_material_code', 'Wajib', 'Kode bahan baku', 'RM-BOX', 'Harus sudah ada.', 'Tidak membuat master bahan.'],
            ['raw_material_name', 'Optional', 'Teks', 'Box Kemasan', 'Informasi saja.', 'Pencocokan tetap memakai code.'],
            ['movement_type', 'Wajib', 'restock|usage|adjustment', 'restock', 'Disesuaikan dengan arah delta.', 'Jenis movement.'],
            ['quantity_delta', 'Kondisional', 'Angka ≠ 0', '100', 'Positif masuk, negatif keluar.', 'Tidak boleh membuat saldo negatif.'],
            ['balance_after', 'Kondisional', 'Angka ≥ 0', '250', 'Dipakai sebagai target mode update.', 'Sistem menghitung delta.'],
            ['unit_cost', 'Restock', 'Angka ≥ 0', '3000', 'Harga per unit pembelian.', 'Dipakai menghitung average cost tertimbang.'],
            ['reference_type', 'Optional', 'Teks', 'purchase', 'Default spreadsheet_import.', 'Jenis sumber movement.'],
            ['reference_number', 'Optional', 'Teks', 'PO-001', 'Maksimum 100 karakter.', 'Nomor sumber.'],
            ['notes', 'Optional', 'Teks', 'Restock bahan', 'Catatan audit.', 'Tidak mengubah master.'],
            ['occurred_at', 'Wajib', 'Tanggal dan waktu', '2026-08-15 10:00:00', 'Format Excel didukung.', 'Waktu movement.'],
        ];
    }

    private static function productCostingExamples(): array
    {
        $base = ['id' => '', 'store_name' => 'Toko Nusantara', 'product_id' => '1', 'product_name' => 'Produk Contoh', 'materials' => 'RM-BOX:1|RM-LABEL:2', 'labor_cost' => '5000', 'overhead_cost' => '2500', 'other_cost' => '1000', 'hpp' => '', 'margin_percent' => '30', 'suggested_price' => '', 'selling_price' => '25000', 'apply_to_variants' => '0'];
        return [
            self::example($base, 'HPP Baru', 'Membentuk HPP dari resep bahan.', 'Pilih create dan isi product serta materials.', 'HPP dihitung dari average cost bahan saat import.'),
            self::example([...$base, 'materials' => 'RM-BOX:2'], 'Satu Bahan', 'Produk hanya memakai satu bahan.', 'Isi satu KODE:QTY.', 'Material cost mengikuti quantity.'),
            self::example([...$base, 'materials' => 'RM-BOX:1.5|RM-LABEL:0.5'], 'Quantity Pecahan', 'Resep memakai quantity pecahan.', 'Gunakan angka desimal.', 'HPP menghitung pecahan.'),
            self::example([...$base, 'labor_cost' => '10000'], 'Tenaga Kerja', 'Menambah biaya tenaga kerja.', 'Isi labor_cost.', 'HPP bertambah.'),
            self::example([...$base, 'overhead_cost' => '8000'], 'Overhead', 'Menambah biaya operasional.', 'Isi overhead_cost.', 'HPP bertambah.'),
            self::example([...$base, 'margin_percent' => '40'], 'Margin', 'Mengubah target margin.', 'Isi margin 40.', 'Saran harga jual diperbarui.'),
            self::example([...$base, 'selling_price' => '30000', 'apply_to_variants' => '1'], 'Terapkan Harga', 'Menerapkan harga jual ke variant.', 'Isi apply_to_variants=1.', 'Harga variant diperbarui.'),
            self::example([...$base, 'id' => '5'], 'Update HPP', 'Memperbarui resep HPP.', 'Pilih update dan isi id HPP.', 'Resep lama diganti secara atomik.'),
            self::example([...$base, 'materials' => 'RM-TIDAK-ADA:1'], 'Bahan Tidak Ada', 'HPP tidak membuat bahan otomatis.', 'Gunakan kode tidak tersedia.', 'Baris ditolak.'),
            self::example([...$base, 'materials' => 'RM-BOX:0'], 'Quantity Salah', 'Quantity harus lebih dari nol.', 'Isi qty 0.', 'Baris ditolak.'),
        ];
    }

    private static function productCostingDescriptions(): array
    {
        return [
            ['id', 'Kondisional', 'Angka bulat', '5', 'Wajib pada mode update bila diisi.', 'ID product_costings.'],
            ['store_name', 'Wajib Admin, otomatis Seller', 'Nama toko', 'Toko Nusantara', 'Harus tersedia.', 'Tidak membuat toko.'],
            ['product_id', 'Disarankan', 'ID Product', '1', 'Harus milik toko.', 'Lebih stabil daripada nama.'],
            ['product_name', 'Fallback', 'Nama produk', 'Produk Contoh', 'Dipakai bila product_id kosong.', 'Harus unik pada toko.'],
            ['materials', 'Optional', 'KODE:QTY|KODE:QTY', 'RM-BOX:1|RM-LABEL:2', 'Semua kode harus tersedia dan qty > 0.', 'Biaya unit selalu average_cost database.'],
            ['labor_cost', 'Optional', 'Angka ≥ 0', '5000', 'Tidak boleh negatif.', 'Biaya tenaga kerja.'],
            ['overhead_cost', 'Optional', 'Angka ≥ 0', '2500', 'Tidak boleh negatif.', 'Biaya overhead.'],
            ['other_cost', 'Optional', 'Angka ≥ 0', '1000', 'Tidak boleh negatif.', 'Biaya lain.'],
            ['hpp', 'Output', 'Angka', '', 'Diabaikan saat import.', 'Dihitung backend.'],
            ['margin_percent', 'Optional', 'Angka ≥ 0', '30', 'Tidak boleh negatif.', 'Dasar suggested price.'],
            ['suggested_price', 'Output', 'Angka', '', 'Diabaikan saat import.', 'Dihitung backend.'],
            ['selling_price', 'Optional', 'Angka ≥ 0', '25000', 'Kosong memakai suggested price.', 'Harga jual pilihan seller.'],
            ['apply_to_variants', 'Optional', '0|1', '0', 'Default 0.', 'Jika 1 harga jual diterapkan ke variant.'],
        ];
    }

    private static function customerDescriptions(): array
    {
        return [
            ['id', 'Output', 'UUID', '', 'Export only.', 'ID buyer.'], ['name', 'Output', 'Teks', '', 'Export only.', 'Nama buyer.'], ['email', 'Output', 'Email', '', 'Export only.', 'Email buyer.'], ['orders_count', 'Output', 'Angka', '', 'Export only.', 'Jumlah order non-cancelled pada toko.'], ['total_spent', 'Output', 'Angka', '', 'Export only.', 'Total transaksi pada toko.'], ['last_order_at', 'Output', 'Tanggal', '', 'Export only.', 'Transaksi terakhir.'], ['is_active', 'Output', '0|1', '', 'Export only.', 'Status akun.'], ['registered_at', 'Output', 'Tanggal', '', 'Export only.', 'Tanggal registrasi buyer.'],
        ];
    }

    private static function costImpactDescriptions(): array
    {
        return [
            ['id', 'Output', 'Angka', '', 'Export only.', 'ID histori dampak.'], ['product_name', 'Output', 'Teks', '', 'Export only.', 'Produk terdampak.'], ['raw_material_code', 'Output', 'Teks', '', 'Export only.', 'Kode bahan pemicu.'], ['raw_material_name', 'Output', 'Teks', '', 'Export only.', 'Bahan pemicu.'], ['old_average_cost', 'Output', 'Angka', '', 'Export only.', 'Biaya rata-rata lama.'], ['new_average_cost', 'Output', 'Angka', '', 'Export only.', 'Biaya rata-rata baru.'], ['old_hpp', 'Output', 'Angka', '', 'Export only.', 'HPP sebelum perubahan.'], ['new_hpp', 'Output', 'Angka', '', 'Export only.', 'HPP sesudah perubahan.'], ['hpp_change_amount', 'Output', 'Angka', '', 'Export only.', 'Selisih HPP.'], ['hpp_change_percent', 'Output', 'Persen', '', 'Export only.', 'Persentase dampak.'], ['old_suggested_price', 'Output', 'Angka', '', 'Export only.', 'Saran harga lama.'], ['new_suggested_price', 'Output', 'Angka', '', 'Export only.', 'Saran harga baru.'], ['direction', 'Output', 'increase|decrease', '', 'Export only.', 'Arah perubahan.'], ['reference_number', 'Output', 'Teks', '', 'Export only.', 'Referensi restock/update.'], ['occurred_at', 'Output', 'Tanggal', '', 'Export only.', 'Waktu dampak.'],
        ];
    }

    private static function exampleRows(array $rows, string $type, string $scenario, string $howTo, string $expected, string $notes = '', string $action = ''): array
    {
        return [
            'data_rows' => $rows,
            'case_type' => $type,
            'scenario' => $scenario,
            'how_to' => $howTo,
            'expected' => $expected,
            'notes' => $notes,
            'user_action' => $action,
        ];
    }

    private static function example(array $data, string $type, string $scenario, string $howTo, string $expected, string $notes = '', string $action = ''): array
    {
        return [
            ...$data,
            'case_type' => $type,
            'scenario' => $scenario,
            'how_to' => $howTo,
            'expected' => $expected,
            'notes' => $notes,
            'user_action' => $action,
        ];
    }
}
