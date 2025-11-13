<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_example_route_returns_success()
    {
        $response = $this->get('/'); // يختبر الصفحة الرئيسية

        $response->assertStatus(200);
    }
}
