<?php

namespace Tests\Unit\Models\Meals;

use App\Models\User;
use App\Models\Profile;
use App\Models\Diet;
use App\Models\Meal;
use App\Models\Food;
use App\Models\FoodMeal;
use Tests\TestCase;

class MealTest extends TestCase
{
    private Meal $meal;


    public function setUp(): void
    {
        parent::setUp();


        $user = new User([
           'name' => 'Fulano',
           'email' => 'fulano@example.com',
           'password' => '123456',
           'password_confirmation' => '123456'
        ]);
        $user->save();


        $profile = new Profile([
           'user_id' => $user->id,
           'height' => 175,
           'birthday' => '2000-05-15',
           'weight' => 70,
           'biotype' => 'ECTOMORFO',
           'gender' => 'M',
           'activity_factor' => '1.550',
           'objective' => 'GANHAR'
        ]);
        $profile->save();


        $diet = Diet::createFromProfile($user, 'Bulking');
        $diet->save();


        $this->meal = new Meal([
           'diet_id' => $diet->id,
           'name' => 'Almoço'
        ]);
        $this->meal->save();


        $banana = new Food([
           'user_id' => $user->id,
           'name' => 'Banana',
           'kcal' => 89,
           'carbs' => 22,
           'fats' => 0.3,
           'protein' => 1.1,
           'unit' => 'g',
           'category' => 'Fruta'
        ]);
        $banana->save();


        $arroz = new Food([
           'user_id' => $user->id,
           'name' => 'Arroz',
           'kcal' => 130,
           'carbs' => 28,
           'fats' => 0.3,
           'protein' => 2.7,
           'unit' => 'g',
           'category' => 'Grão'
        ]);
        $arroz->save();


        (new FoodMeal([
           'meal_id' => $this->meal->id,
           'food_id' => $banana->id,
           'quantity' => 150
        ]))->save();


        (new FoodMeal([
           'meal_id' => $this->meal->id,
           'food_id' => $arroz->id,
           'quantity' => 200
        ]))->save();
    }


    public function test_food_meals_relationship(): void
    {
        $this->assertCount(
            2,
            $this->meal->foodMeals()->get()
        );
    }


    public function test_items(): void
    {
        $items = $this->meal->items();


        $this->assertCount(2, $items);
        $this->assertEquals('Banana', $items[0]['food']->name);
    }


    public function test_totals(): void
    {
        $totals = $this->meal->totals();


        $this->assertEqualsWithDelta(
            393.5,
            $totals['kcal'],
            0.01
        );


        $this->assertEqualsWithDelta(
            89,
            $totals['carbs'],
            0.01
        );
    }


    public function test_diet_relationship(): void
    {
        $this->assertEquals(
            $this->meal->diet_id,
            $this->meal->diet->id
        );
    }
}
