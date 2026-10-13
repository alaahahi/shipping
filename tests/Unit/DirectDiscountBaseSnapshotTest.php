<?php

namespace Tests\Unit;

use App\Models\Car;
use App\Models\SystemConfig;
use Tests\TestCase;

class DirectDiscountBaseSnapshotTest extends TestCase
{
    public function test_resolve_uses_stored_snapshot_or_falls_back_to_130000(): void
    {
        $oldCar = new Car(['direct_discount_base' => 130000]);
        $this->assertSame(130000, $oldCar->resolveDirectDiscountBase());

        $newCar = new Car(['direct_discount_base' => 140000]);
        $this->assertSame(140000, $newCar->resolveDirectDiscountBase());

        $missing = new Car(['direct_discount_base' => null]);
        $this->assertSame(SystemConfig::DEFAULT_DIRECT_DISCOUNT_BASE, $missing->resolveDirectDiscountBase());
    }
}
