<?php

namespace Database\Factories;

use App\Models\InternalSale;
use App\Models\User;
use App\Models\Car;
use Illuminate\Database\Eloquent\Factories\Factory;

class InternalSaleFactory extends Factory
{
    protected $model = InternalSale::class;

    public function definition(): array
    {
        return [
            'client_id' => User::factory(),
            'car_id' => Car::factory(),
            'sale_price' => $this->faker->randomFloat(2, 5000, 50000),
            'paid_amount' => $this->faker->randomFloat(2, 0, 30000),
            'expenses' => $this->faker->randomFloat(2, 0, 5000),
            'note' => $this->faker->optional()->sentence(),
            'sale_date' => $this->faker->date(),
        ];
    }
}

