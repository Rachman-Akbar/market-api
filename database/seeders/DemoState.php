<?php

declare(strict_types=1);

namespace Database\Seeders;

/**
 * Penyimpanan ringan antar seeder selama satu proses seeding.
 * (Struktur DB tidak diubah; hanya pemegang nilai sementara.)
 */
final class DemoState
{
    /** @var array<string, mixed> */
    private static array $state = [];

    public static function set(string $key, mixed $value): void
    {
        self::$state[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$state[$key] ?? $default;
    }

    /** @param array<string, int> $ids */
    public static function storeIds(array $ids): void
    {
        self::$state['store_ids'] = $ids;
    }

    public static function storeId(string $slug, ?int $default = null): ?int
    {
        return self::$state['store_ids'][$slug] ?? $default;
    }

    public static function storeOwner(string $slug, ?string $ownerId = null): ?string
    {
        if ($ownerId !== null) {
            self::$state['store_owners'][$slug] = $ownerId;
        }

        return self::$state['store_owners'][$slug] ?? null;
    }
}
