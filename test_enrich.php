<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$cars = [
    ['id' => 1, 'car_number' => 'ABC'],
    ['id' => 2, 'car_number' => 'DEF'],
];

$result = App\Http\Controllers\CarExpensesController::enrichCarsWithLinkRates($cars);
foreach ($result as $car) {
    echo json_encode($car, JSON_UNESCAPED_UNICODE) . PHP_EOL;
}
echo "OK\n";
