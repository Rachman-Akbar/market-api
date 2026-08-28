<?php

namespace Tests;

use Illuminate\Support\Facades\DB;

/**
 * Base test case for integration tests that run against the real (MySQL)
 * database used by the application. Tests MUST be self-cleaning: every test
 * only creates rows with a unique/tagged prefix and removes them afterwards.
 * This intentionally does NOT call RefreshDatabase to avoid destructive
 * operations on the shared marketplaceku database.
 */
abstract class IntegrationTestCase extends \Tests\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Force the real MySQL connection regardless of phpunit.xml sqlite override.
        config()->set('database.default', 'mysql');
        DB::purge('mysql');
        DB::reconnect('mysql');

        $this->beginDatabaseTransaction = false;
    }
}
