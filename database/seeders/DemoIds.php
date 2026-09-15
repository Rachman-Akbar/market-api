<?php

declare(strict_types=1);

namespace Database\Seeders;

/**
 * Kunci ID pengguna (UUID) yang stabil untuk seluruh data demo.
 * Tetap sama setiap kali seeder dijalankan agar relasi antar data konsisten.
 */
final class DemoIds
{
    public const SUPER_ADMIN = '00000000-0000-4000-8000-000000000001';

    public const ADMIN_CATALOG = '00000000-0000-4000-8000-000000000002';

    public const SELLER_SARI = '00000000-0000-4000-8000-000000000101';

    public const SELLER_RAKA = '00000000-0000-4000-8000-000000000102';

    public const SELLER_KOPI = '00000000-0000-4000-8000-000000000103';

    public const SELLER_PURNAMA = '00000000-0000-4000-8000-000000000104';

    public const SELLER_ANANDA = '00000000-0000-4000-8000-000000000105';

    public const SELLER_OPS = '00000000-0000-4000-8000-000000000106';

    public const BUYER_1 = '00000000-0000-4000-8000-000000000201';

    public const BUYER_2 = '00000000-0000-4000-8000-000000000202';

    public const BUYER_3 = '00000000-0000-4000-8000-000000000203';

    public const BUYER_4 = '00000000-0000-4000-8000-000000000204';

    public const BUYER_5 = '00000000-0000-4000-8000-000000000205';

    public const BUYER_6 = '00000000-0000-4000-8000-000000000206';

    public const BUYER_7 = '00000000-0000-4000-8000-000000000207';

    public const BUYER_8 = '00000000-0000-4000-8000-000000000208';

    public const BUYER_9 = '00000000-0000-4000-8000-000000000209';

    public const BUYER_10 = '00000000-0000-4000-8000-000000000210';

    public const PASSWORD = '12345678';

    /** Semua id buyer untuk iterasi. */
    public const BUYERS = [
        self::BUYER_1,
        self::BUYER_2,
        self::BUYER_3,
        self::BUYER_4,
        self::BUYER_5,
        self::BUYER_6,
        self::BUYER_7,
        self::BUYER_8,
        self::BUYER_9,
        self::BUYER_10,
    ];

    /** Semua id seller (5 seller jualan + 1 seller operasional). */
    public const SELLERS = [
        self::SELLER_SARI,
        self::SELLER_RAKA,
        self::SELLER_KOPI,
        self::SELLER_PURNAMA,
        self::SELLER_ANANDA,
        self::SELLER_OPS,
    ];
}