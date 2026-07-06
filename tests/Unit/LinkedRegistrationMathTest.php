<?php

namespace Tests\Unit;

use App\Http\Controllers\CarExpensesController;
use App\Models\Car;
use App\Models\CarExpenses;
use Illuminate\Support\Collection;
use Tests\TestCase;

class LinkedRegistrationMathTest extends TestCase
{
    public function test_compute_linked_total_for_sample_2890000_at_153000(): void
    {
        $expenses = collect([
            (object) ['amount_dinar' => 2890000, 'amount_dollar' => 0],
        ]);

        $usd = CarExpensesController::computeLinkedRegistrationTotal($expenses, 153000);

        $this->assertSame(1888, $usd);
    }

    public function test_compute_linked_total_changes_when_rate_changes(): void
    {
        $expenses = collect([
            (object) ['amount_dinar' => 2890000, 'amount_dollar' => 0],
        ]);

        $at153 = CarExpensesController::computeLinkedRegistrationTotal($expenses, 153000);
        $at160 = CarExpensesController::computeLinkedRegistrationTotal($expenses, 160000);

        $this->assertSame(1888, $at153);
        $this->assertSame(1806, $at160);
        $this->assertNotSame($at153, $at160);
    }

    public function test_target_expenses_subtract_old_linked_and_add_new_for_both_sides(): void
    {
        $car = new Car([
            'expenses' => 5000,
            'expenses_s' => 4800,
            'registration_pre_expenses' => 3112,
            'registration_pre_expenses_s' => 2912,
            'registration_linked_usd' => 1888,
            'registration_exchange_rate' => 153000,
            'car_have_expenses' => 2,
        ]);

        $car->setRelation('carexpenses', collect([
            new CarExpenses([
                'amount_dinar' => 2890000,
                'amount_dollar' => 0,
                'note' => 'test [مربوط]',
            ]),
        ]));

        $targetsOld = CarExpensesController::computeTargetRegistrationExpenses($car, 153000);
        $this->assertSame(1888, $targetsOld['linked_usd']);
        $this->assertSame(5000, $targetsOld['expenses']);
        $this->assertSame(4800, $targetsOld['expenses_s']);

        $targetsNew = CarExpensesController::computeTargetRegistrationExpenses($car, 160000);
        $this->assertSame(1806, $targetsNew['linked_usd']);
        $this->assertSame(3112 + 1806, $targetsNew['expenses']);
        $this->assertSame(2912 + 1806, $targetsNew['expenses_s']);
        $this->assertSame(4918, $targetsNew['expenses']);
        $this->assertSame(4718, $targetsNew['expenses_s']);
    }

    public function test_archive_car_with_linked_notes_is_treated_as_linked(): void
    {
        $car = new Car([
            'car_have_expenses' => 2,
            'registration_exchange_rate' => 153000,
        ]);

        $car->setRelation('carexpenses', collect([
            new CarExpenses(['note' => 'بند [مربوط]']),
        ]));

        $this->assertTrue(CarExpensesController::isCarLinked($car));
    }

    public function test_resolve_stored_linked_usd_from_pre_purchases(): void
    {
        $car = new Car([
            'expenses' => 5000,
            'expenses_s' => 5000,
            'registration_pre_expenses' => 3112,
            'registration_pre_expenses_s' => 3112,
        ]);
        $car->setRelation('carexpenses', collect());

        $this->assertSame(1888, CarExpensesController::resolveStoredLinkedUsd($car));
    }

    public function test_parse_contract_note_part(): void
    {
        $parsed = CarExpensesController::parseRegistrationNotePart('عقد 200,000 د');

        $this->assertSame('contract', $parsed['type']);
        $this->assertSame('dinar', $parsed['currency']);
        $this->assertSame(200000.0, $parsed['amount']);
    }

    public function test_format_contract_line_item(): void
    {
        $line = CarExpensesController::formatRegistrationLineItem('contract', 'dinar', 200000);

        $this->assertSame('عقد 200,000 د', $line);
    }

    public function test_car_linked_via_db_meta_without_note_tag(): void
    {
        $car = new Car([
            'car_have_expenses' => CarExpensesController::CAR_HAVE_EXPENSES_LINKED,
            'registration_exchange_rate' => 153000,
            'registration_linked_usd' => 1888,
            'registration_pre_expenses' => 3112,
            'registration_pre_expenses_s' => 2912,
        ]);

        $car->setRelation('carexpenses', collect([
            new CarExpenses([
                'amount_dinar' => 2890000,
                'amount_dollar' => 0,
                'note' => 'تسجيل 2,890,000 د',
            ]),
        ]));

        $this->assertTrue(CarExpensesController::isCarLinked($car));
        $linked = CarExpensesController::resolveLinkedRegistrationExpenses($car);
        $this->assertCount(1, $linked);
        $this->assertSame(
            1888,
            CarExpensesController::computeLinkedRegistrationTotal($linked, 153000)
        );
    }
}
