<?php

namespace Tests\Unit\Support;

use App\Support\DatabaseDriver;
use Tests\TestCase;

class DatabaseDriverTest extends TestCase
{
    public function test_sqlite_json_helpers(): void
    {
        $this->assertTrue(DatabaseDriver::isSqlite());
        $this->assertStringContainsString('json_extract', DatabaseDriver::jsonStringExtract('details', 'driver_name'));
        $this->assertStringContainsString('= 1', DatabaseDriver::jsonTruthy('details', 'loan'));
    }
}
