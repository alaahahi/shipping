<?php

namespace App\Monitor\Http\Controllers;

use App\Monitor\Services\DbStatusService;
use Illuminate\Http\JsonResponse;

class StatusController
{
    public function __invoke(DbStatusService $dbStatus): JsonResponse
    {
        $snapshot = $dbStatus->snapshot(true);

        return response()->json([
            'project' => config('monitor.project_name'),
            'database' => $snapshot['database'] ?? null,
            'threads_connected' => $snapshot['threads_connected'] ?? 0,
            'threads_running' => $snapshot['threads_running'] ?? 0,
            'connections' => $snapshot['connections'] ?? 0,
            'max_used_connections' => $snapshot['max_used_connections'] ?? 0,
            'memory' => $dbStatus->formatMemory(),
            'uptime' => $snapshot['uptime'] ?? 0,
        ]);
    }
}
