<?php

namespace Tests\Feature\Monitor;

use App\Models\User;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class MonitorEndpointsTest extends TestCase
{
    protected function makeAdminUser(): User
    {
        $user = new User([
            'name' => 'Monitor Admin',
            'email' => 'monitor-admin@example.com',
            'type_id' => 1,
        ]);
        $user->id = 999001;

        return $user;
    }

    public function test_status_endpoint_requires_admin(): void
    {
        $this->get('/monitor/status')->assertRedirect();
    }

    public function test_dashboard_endpoint_requires_admin(): void
    {
        $this->get('/monitor/dashboard')->assertRedirect();
    }

    public function test_status_endpoint_returns_json_for_admin(): void
    {
        $response = $this->actingAs($this->makeAdminUser())
            ->getJson('/monitor/status');

        $response->assertOk()
            ->assertJsonStructure([
                'project',
                'database',
                'threads_connected',
                'threads_running',
                'connections',
                'max_used_connections',
                'memory',
                'uptime',
            ]);
    }

    public function test_dashboard_renders_for_admin(): void
    {
        config(['monitor.log_path' => storage_path('logs/monitor')]);

        $response = $this->actingAs($this->makeAdminUser())
            ->get('/monitor/dashboard');

        $response->assertOk()
            ->assertSee('مراقبة النظام', false);
    }

    public function test_non_admin_is_denied(): void
    {
        $user = $this->makeAdminUser();
        $user->type_id = 99;

        $this->actingAs($user)
            ->get('/monitor/dashboard')
            ->assertForbidden();
    }
}
