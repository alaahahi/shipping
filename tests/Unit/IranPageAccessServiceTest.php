<?php

namespace Tests\Unit;

use App\Services\IranPageAccessService;
use Tests\TestCase;

class IranPageAccessServiceTest extends TestCase
{
    /** @test */
    public function pin_matches_using_hashed_comparison()
    {
        $service = app(IranPageAccessService::class);

        $this->assertTrue($service->pinMatches('134679'));
        $this->assertFalse($service->pinMatches('000000'));
        $this->assertFalse($service->pinMatches(''));
    }
}
