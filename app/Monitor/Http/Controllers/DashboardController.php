<?php

namespace App\Monitor\Http\Controllers;

use App\Monitor\Services\LogReader;
use App\Monitor\Services\MetricsAggregator;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController
{
    public function __invoke(Request $request, LogReader $reader, MetricsAggregator $aggregator): View
    {
        $date = $request->query('date', now()->format('Y-m-d'));
        $records = $reader->readDailyLog($date);
        $metrics = $aggregator->aggregate($records);
        $alerts = array_slice(array_reverse($reader->readAlerts()), 0, 100);

        return view('monitor.dashboard', [
            'date' => $date,
            'availableDates' => $reader->listDailyFiles(),
            'metrics' => $metrics,
            'alerts' => $alerts,
            'project' => config('monitor.project_name'),
        ]);
    }
}
