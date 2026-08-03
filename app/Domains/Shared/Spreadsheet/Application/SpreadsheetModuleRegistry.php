<?php

declare(strict_types=1);

namespace App\Domains\Shared\Spreadsheet\Application;

use App\Domains\Catalog\Banner\Infrastructure\Persistence\Models\BannerModel;
use App\Domains\Catalog\CatalogGroup\Infrastructure\Persistence\Models\CatalogGroupModel;
use App\Domains\Catalog\Category\Infrastructure\Persistence\Models\CategoryModel;
use App\Domains\Catalog\Product\Infrastructure\Persistence\Models\ProductModel;
use App\Domains\Catalog\Promotion\Infrastructure\Persistence\Models\PromotionModel;
use App\Domains\Order\Voucher\Domain\Entities\Voucher;
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
                    'id', 'store_name', 'catalog_group_name', 'name', 'slug', 'description', 'brand', 'primary_category_name', 'category_names', 'status', 'is_active', 'thumbnail', 'image_url', 'image_alt', 'sku', 'variant_name', 'price', 'stock', 'is_default',
                ],
                'examples' => self::productExamples(),
                'guides' => [
                    ['Produk dijual tanpa pilihan', 'Isi satu baris variant bernama Default, price, stock, dan is_default=1. SKU boleh dikosongkan karena backend akan membentuk SKU unik otomatis.'],
                    ['Produk benar-benar tanpa variant', 'Kosongkan sku, variant_name, price, stock, dan is_default. Hanya data Product yang dibuat. Produk belum dapat dibeli sampai variant ditambahkan.'],
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
            'id' => '', 'store_name' => 'Toko Nusantara', 'catalog_group_name' => 'Kebutuhan Harian', 'name' => 'Kecap Manis Premium 600 ml', 'slug' => '', 'description' => 'Kecap manis botol 600 ml untuk kebutuhan keluarga', 'brand' => 'Rasa Kita', 'primary_category_name' => 'Kecap', 'category_names' => 'Kecap, Bumbu Masak', 'status' => 'published', 'is_active' => '1', 'thumbnail' => 'https://example.com/kecap-600.jpg', 'image_url' => 'https://example.com/kecap-600-detail.jpg', 'image_alt' => 'Kecap Manis Premium 600 ml', 'sku' => '', 'variant_name' => 'Default', 'price' => '25000', 'stock' => '100', 'is_default' => '1',
        ];

        return [
            self::example($simpleAutoSku, 'Product Baru Sederhana', 'Toko menjual satu Product dengan satu harga dan tidak memiliki pilihan warna atau ukuran.', 'Pilih Import Data Baru. Kosongkan id dan sku, isi variant_name=Default, price, stock, serta is_default=1.', 'Product baru dan default variant dibuat. Backend membentuk SKU unik otomatis.', 'Ini pola yang disarankan untuk barang biasa yang hanya memiliki satu harga.'),
            self::example([
                ...$simpleAutoSku, 'name' => 'Kecap Manis Refill 500 ml', 'description' => 'Kecap manis kemasan refill', 'thumbnail' => 'https://example.com/kecap-refill.jpg', 'image_url' => '', 'image_alt' => 'Kecap Manis Refill 500 ml', 'sku' => 'KECAP-REFILL-500', 'price' => '18000', 'stock' => '80',
            ], 'Product Baru dengan SKU Manual', 'Toko mempunyai kode SKU internal sendiri untuk Product sederhana.', 'Pilih Import Data Baru, kosongkan id, lalu isi SKU manual yang belum pernah digunakan di toko tersebut.', 'Product dibuat menggunakan SKU KECAP-REFILL-500.', 'SKU manual harus unik dalam file dan unik pada toko yang sama.'),
            self::exampleRows([
                ['id' => '', 'store_name' => 'Toko Nusantara', 'catalog_group_name' => 'Fashion', 'name' => 'Kaos Polos Premium', 'slug' => '', 'description' => 'Kaos katun combed 30s', 'brand' => 'Basic Wear', 'primary_category_name' => 'Kaos', 'category_names' => 'Kaos, Pakaian', 'status' => 'published', 'is_active' => '1', 'thumbnail' => 'https://example.com/kaos.jpg', 'image_url' => '', 'image_alt' => 'Kaos Polos Premium', 'sku' => '', 'variant_name' => 'Hitam - M', 'price' => '85000', 'stock' => '20', 'is_default' => '1'],
                ['id' => '', 'store_name' => 'Toko Nusantara', 'catalog_group_name' => 'Fashion', 'name' => 'Kaos Polos Premium', 'slug' => '', 'description' => 'Kaos katun combed 30s', 'brand' => 'Basic Wear', 'primary_category_name' => 'Kaos', 'category_names' => 'Kaos, Pakaian', 'status' => 'published', 'is_active' => '1', 'thumbnail' => 'https://example.com/kaos.jpg', 'image_url' => '', 'image_alt' => 'Kaos Polos Premium', 'sku' => '', 'variant_name' => 'Hitam - L', 'price' => '85000', 'stock' => '15', 'is_default' => '0'],
                ['id' => '', 'store_name' => 'Toko Nusantara', 'catalog_group_name' => 'Fashion', 'name' => 'Kaos Polos Premium', 'slug' => '', 'description' => 'Kaos katun combed 30s', 'brand' => 'Basic Wear', 'primary_category_name' => 'Kaos', 'category_names' => 'Kaos, Pakaian', 'status' => 'published', 'is_active' => '1', 'thumbnail' => 'https://example.com/kaos.jpg', 'image_url' => '', 'image_alt' => 'Kaos Polos Premium', 'sku' => '', 'variant_name' => 'Putih - M', 'price' => '82000', 'stock' => '12', 'is_default' => '0'],
            ], 'Product Baru Multi-Variant', 'Satu Product Kaos memiliki beberapa pilihan warna dan ukuran.', 'Pilih Import Data Baru. Gunakan beberapa baris dengan store_name dan name yang sama, ulangi data utama, lalu bedakan variant_name. SKU boleh kosong dan hanya satu baris memakai is_default=1.', 'Satu Product dibuat dengan tiga variant dan tiga SKU unik otomatis.', 'Jangan mengubah nama Product pada baris variant berikutnya. Variant name dalam Product yang sama tidak boleh kembar.'),
            self::example([
                'id' => '', 'store_name' => 'Toko Nusantara', 'catalog_group_name' => 'Informasi', 'name' => 'Layanan Pesanan Khusus', 'slug' => '', 'description' => 'Halaman informasi untuk pesanan yang memerlukan konsultasi', 'brand' => '', 'primary_category_name' => 'Informasi', 'category_names' => 'Informasi', 'status' => 'draft', 'is_active' => '1', 'thumbnail' => '', 'image_url' => '', 'image_alt' => '', 'sku' => '', 'variant_name' => '', 'price' => '', 'stock' => '', 'is_default' => '',
            ], 'Product Tanpa Variant', 'Admin ingin menyiapkan Product informasi yang belum mempunyai harga dan stok.', 'Pilih Import Data Baru dan kosongkan seluruh kolom variant: sku, variant_name, price, stock, serta is_default.', 'Hanya Product draft yang dibuat tanpa record variant.', 'Product belum dapat dibeli sampai variant, harga, dan stok ditambahkan.'),
            self::example([
                'id' => '', 'store_name' => 'Toko Nusantara', 'catalog_group_name' => 'Makanan', 'name' => 'Sambal Bawang 200 ml', 'slug' => '', 'description' => 'Sambal bawang rumahan', 'brand' => 'Dapur Ibu', 'primary_category_name' => 'Sambal', 'category_names' => 'Sambal, Bumbu Masak', 'status' => 'published', 'is_active' => '1', 'thumbnail' => 'https://example.com/sambal-thumb.webp', 'image_url' => 'products/sambal-detail.webp', 'image_alt' => 'Sambal Bawang 200 ml', 'sku' => '', 'variant_name' => 'Default', 'price' => '22000', 'stock' => '40', 'is_default' => '1',
            ], 'Product dengan Gambar', 'Product memakai gambar utama dari URL dan gambar detail dari storage atau gambar yang ditempel ke cell.', 'Isi thumbnail/image_url dengan URL atau path. Gambar juga dapat ditempel tepat pada cell dan baris terkait.', 'Gambar disimpan atau dinormalisasi ke storage Laravel, lalu Product dibuat dengan SKU otomatis.', 'Gunakan gambar JPG, JPEG, PNG, atau WEBP dengan ukuran yang wajar.'),
            self::example([
                'id' => '', 'store_name' => 'Toko Nusantara', 'catalog_group_name' => 'Kuliner Tradisional', 'name' => 'Tape Singkong Manis', 'slug' => '', 'description' => 'Tape singkong segar', 'brand' => 'Dapur Desa', 'primary_category_name' => 'Tape', 'category_names' => 'Tape, Makanan Fermentasi', 'status' => 'draft', 'is_active' => '1', 'thumbnail' => '', 'image_url' => '', 'image_alt' => '', 'sku' => '', 'variant_name' => '500 gram', 'price' => '15000', 'stock' => '25', 'is_default' => '1',
            ], 'Product dengan Relasi Baru', 'Catalog Group Kuliner Tradisional dan Category Tape belum tersedia saat Product diimport.', 'Pilih Import Data Baru dan isi nama relasi yang diinginkan. Jalankan validasi import.', 'Permintaan pembuatan Catalog Group dan Category masuk ke tab Antrean untuk dipilih Lanjutkan atau Batal.', 'Relasi aman dapat dibuat otomatis setelah disetujui; Toko tidak dibuat otomatis.'),
            self::example([
                'id' => '', 'store_name' => '  toko nusantara ', 'catalog_group_name' => ' makanan ', 'name' => 'Keripik Singkong Balado', 'slug' => '', 'description' => 'Keripik singkong balado', 'brand' => 'Cemilan Kita', 'primary_category_name' => ' keripik ', 'category_names' => ' Keripik , Cemilan , Makanan Pedas ', 'status' => 'PUBLISHED', 'is_active' => 'YA', 'thumbnail' => '', 'image_url' => '', 'image_alt' => '', 'sku' => '', 'variant_name' => 'Default', 'price' => '12000', 'stock' => '75', 'is_default' => 'YA',
            ], 'Product Banyak Category', 'Product mempunyai Category utama dan beberapa Category tambahan, sementara kapital dan spasi data sumber tidak konsisten.', 'Pisahkan category_names dengan koma. Sistem membersihkan spasi serta mencocokkan nama tanpa peka kapital.', 'Relasi lama dipakai tanpa membuat duplikasi dan SKU dibentuk otomatis.', 'Kapitalisasi nama yang sudah tersimpan di database tetap dipertahankan.'),
            self::example([
                ...$simpleAutoSku, 'id' => '109', 'name' => 'Kecap Manis Premium 600 ml', 'description' => 'Deskripsi dan harga diperbarui', 'sku' => 'KECAP-600', 'price' => '27000', 'stock' => '90',
            ], 'Update Product dan Variant', 'Product ID 109 dan variant dengan SKU KECAP-600 sudah tersedia dan perlu diperbarui.', 'Pilih Import Update Data, isi id Product, gunakan SKU lama untuk menunjuk variant, lalu isi nilai terbaru.', 'Product ID 109 dan variant yang sesuai diperbarui tanpa membuat Product baru.', 'Mode Update mewajibkan id. SKU milik Product lain akan ditolak.'),
            self::exampleRows([
                ['id' => '109', 'store_name' => 'Toko Nusantara', 'catalog_group_name' => 'Kebutuhan Harian', 'name' => 'Kecap Manis Premium 600 ml', 'slug' => '', 'description' => 'Menambah ukuran baru', 'brand' => 'Rasa Kita', 'primary_category_name' => 'Kecap', 'category_names' => 'Kecap, Bumbu Masak', 'status' => 'published', 'is_active' => '1', 'thumbnail' => 'https://example.com/kecap-600.jpg', 'image_url' => '', 'image_alt' => 'Kecap Manis Premium', 'sku' => 'KECAP-600', 'variant_name' => '600 ml', 'price' => '27000', 'stock' => '90', 'is_default' => '1'],
                ['id' => '109', 'store_name' => 'Toko Nusantara', 'catalog_group_name' => 'Kebutuhan Harian', 'name' => 'Kecap Manis Premium 600 ml', 'slug' => '', 'description' => 'Menambah ukuran baru', 'brand' => 'Rasa Kita', 'primary_category_name' => 'Kecap', 'category_names' => 'Kecap, Bumbu Masak', 'status' => 'published', 'is_active' => '1', 'thumbnail' => 'https://example.com/kecap-600.jpg', 'image_url' => '', 'image_alt' => 'Kecap Manis Premium', 'sku' => '', 'variant_name' => '1 Liter', 'price' => '42000', 'stock' => '30', 'is_default' => '0'],
            ], 'Update Product dan Tambah Variant', 'Product lama tetap diperbarui sekaligus mendapat variant ukuran 1 Liter yang belum tersedia.', 'Pilih Import Update Data dan isi id yang sama pada kedua baris. Gunakan SKU lama untuk variant lama; kosongkan SKU pada variant baru agar backend membuatnya otomatis.', 'Variant lama diperbarui dan variant 1 Liter ditambahkan dengan SKU baru tanpa mengganti default.', 'Variant baru tetap berada pada Product ID yang sama.'),
            self::exampleRows([
                ['id' => '', 'store_name' => 'Toko Nusantara', 'catalog_group_name' => 'Makanan', 'name' => 'Madu Hutan 250 ml', 'slug' => '', 'description' => 'Madu hutan', 'brand' => 'Alam', 'primary_category_name' => 'Madu', 'category_names' => 'Madu', 'status' => 'published', 'is_active' => '1', 'thumbnail' => '', 'image_url' => '', 'image_alt' => '', 'sku' => 'MADU-250', 'variant_name' => '250 ml', 'price' => '65000', 'stock' => '20', 'is_default' => '1'],
                ['id' => '', 'store_name' => 'Toko Nusantara', 'catalog_group_name' => 'Makanan', 'name' => 'Madu Hutan 500 ml', 'slug' => '', 'description' => 'Madu hutan', 'brand' => 'Alam', 'primary_category_name' => 'Madu', 'category_names' => 'Madu', 'status' => 'published', 'is_active' => '1', 'thumbnail' => '', 'image_url' => '', 'image_alt' => '', 'sku' => 'MADU-250', 'variant_name' => '500 ml', 'price' => '110000', 'stock' => '10', 'is_default' => '1'],
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
            ['stock', 'Wajib saat membuat variant', 'Angka bulat ≥ 0', '100', 'Stok disimpan pada variant.', 'Kosong hanya untuk Product tanpa variant.'],
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
