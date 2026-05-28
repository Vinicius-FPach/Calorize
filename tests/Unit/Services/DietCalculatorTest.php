<?php

namespace Tests\Unit\Services;

use App\Models\Profile;
use App\Services\DietCalculator;
use Tests\TestCase;

class DietCalculatorTest extends TestCase
{
    /** @param array<string, mixed> $overrides */
    private function makeProfile(array $overrides = []): Profile
    {
        $defaults = [
            'height'          => 175,
            'birthday'        => '15/05/2000',
            'weight'          => '70.00',
            'biotype'         => 'ECTOMORFO',
            'gender'          => 'M',
            'activity_factor' => '1.550',
            'objective'       => 'MANTER',
        ];

        return new Profile(array_merge($defaults, $overrides));
    }

    public function test_tmb_male(): void
    {
        $profile = $this->makeProfile(['gender' => 'M']);
        $calculator = new DietCalculator($profile);
        $expected = 66.5 + (13.75 * 70) + (5.003 * 175) - (6.75 * $profile->age());
        $this->assertEquals($expected, $calculator->tmb());
    }

    public function test_tmb_female(): void
    {
        $profile = $this->makeProfile(['gender' => 'F']);
        $calculator = new DietCalculator($profile);
        $expected = 655.1 + (9.563 * 70) + (1.850 * 175) - (4.676 * $profile->age());
        $this->assertEquals($expected, $calculator->tmb());
    }

    public function test_tmb_not_informed_should_be_average(): void
    {
        $profile = $this->makeProfile(['gender' => 'NI']);
        $calculator = new DietCalculator($profile);
        $tmbM = 66.5 + (13.75 * 70) + (5.003 * 175) - (6.75 * $profile->age());
        $tmbF = 655.1 + (9.563 * 70) + (1.850 * 175) - (4.676 * $profile->age());
        $expected = ($tmbM + $tmbF) / 2;
        $this->assertEquals($expected, $calculator->tmb());
    }

    public function test_get_should_multiply_tmb_by_activity_factor(): void
    {
        $calculator = new DietCalculator($this->makeProfile());
        $expected = $calculator->tmb() * 1.550;
        $this->assertEquals($expected, $calculator->get());
    }

    public function test_kcal_goal_should_subtract_500_when_emagrecer(): void
    {
        $calculator = new DietCalculator($this->makeProfile(['objective' => 'EMAGRECER']));
        $expected = round($calculator->get() - 500, 2);
        $this->assertEquals($expected, $calculator->kcalGoal());
    }

    public function test_kcal_goal_should_add_300_when_ganhar(): void
    {
        $calculator = new DietCalculator($this->makeProfile(['objective' => 'GANHAR']));
        $expected = round($calculator->get() + 300, 2);
        $this->assertEquals($expected, $calculator->kcalGoal());
    }

    public function test_kcal_goal_should_equal_get_when_manter(): void
    {
        $calculator = new DietCalculator($this->makeProfile(['objective' => 'MANTER']));
        $expected = round($calculator->get(), 2);
        $this->assertEquals($expected, $calculator->kcalGoal());
    }

    public function test_protein_should_be_2g_per_kg_when_emagrecer(): void
    {
        $calculator = new DietCalculator($this->makeProfile(['objective' => 'EMAGRECER']));
        $expected = round(2.0 * 70, 2);
        $this->assertEquals($expected, $calculator->protein());
    }

    public function test_protein_should_be_2g_per_kg_when_ganhar(): void
    {
        $calculator = new DietCalculator($this->makeProfile(['objective' => 'GANHAR']));
        $expected = round(2.0 * 70, 2);
        $this->assertEquals($expected, $calculator->protein());
    }

    public function test_protein_should_be_1_6g_per_kg_when_manter(): void
    {
        $calculator = new DietCalculator($this->makeProfile(['objective' => 'MANTER']));
        $expected = round(1.6 * 70, 2);
        $this->assertEquals($expected, $calculator->protein());
    }

    public function test_fat_should_be_25_percent_of_get(): void
    {
        $calculator = new DietCalculator($this->makeProfile());
        $expected = round(($calculator->get() * 0.25) / 9, 2);
        $this->assertEquals($expected, $calculator->fat());
    }

    public function test_carbs_should_fill_remaining_kcal(): void
    {
        $calculator = new DietCalculator($this->makeProfile());
        $proteinKcal = $calculator->protein() * 4;
        $fatKcal = $calculator->fat() * 9;
        $expected = round(($calculator->kcalGoal() - $proteinKcal - $fatKcal) / 4, 2);
        $this->assertEquals($expected, $calculator->carbs());
    }
}
