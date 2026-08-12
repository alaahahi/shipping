<?php

namespace App\Support;

use App\Models\Car;
use App\Services\CarExpenseBreakdownService;

class CarNoteFormatter
{
    /**
     * @return array<int, string>
     */
    public static function expensePrintLines(Car $car, string $side = 'sales'): array
    {
        $breakdown = $car->expenses_breakdown;

        if (CarExpenseBreakdownService::hasBreakdown($breakdown)) {
            return CarExpenseBreakdownService::linesForPrint($breakdown, $side);
        }

        return CarExpenseBreakdownService::expenseLinesFromNote($car->note);
    }
}
