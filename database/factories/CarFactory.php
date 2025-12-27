<?php

namespace Database\Factories;

use App\Models\Car;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CarFactory extends Factory
{
    protected $model = Car::class;

    public function definition(): array
    {
        static $nextNo = 1;

        return [
            'no' => $nextNo++,
            'client_id' => User::factory(),
            'car_type' => $this->faker->randomElement(['Toyota', 'Honda', 'BMW', 'Mercedes', 'Nissan']),
            'year' => $this->faker->year(),
            'vin' => $this->faker->unique()->regexify('[A-Z0-9]{17}'),
            'car_number' => $this->faker->optional()->regexify('[0-9]{5}'),
            'total_s' => $this->faker->randomFloat(2, 10000, 50000),
            'paid' => $this->faker->randomFloat(2, 0, 30000),
            'discount' => $this->faker->randomFloat(2, 0, 5000),
            'results' => $this->faker->randomElement([0, 1, 2]),
            'date' => $this->faker->date(),
            'owner_id' => 1, // إضافة owner_id المطلوب
        ];
    }
}

