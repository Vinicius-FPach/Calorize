<?php

namespace Tests\Unit\Models\Meals;

use App\Models\User;
use App\Models\Profile;
use App\Models\Diet;
use App\Models\Meal;
use App\Models\Food;
use App\Models\FoodMeal;
use Tests\TestCase;

class FoodMealTest extends TestCase
{
    private FoodMeal $foodMeal;
    private Meal $meal;
    private Food $food;


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


        $this->food = new Food([
           'user_id' => $user->id,
           'name' => 'Banana',
           'kcal' => 89,
           'carbs' => 22,
           'fats' => 0.3,
           'protein' => 1.1,
           'unit' => 'g',
           'category' => 'Fruta'
        ]);
        $this->food->save();


        $this->foodMeal = new FoodMeal([
           'meal_id' => $this->meal->id,
           'food_id' => $this->food->id,
           'quantity' => 150
        ]);


        $this->foodMeal->save();
    }


    public function test_should_create_food_meal(): void
    {
        $this->assertGreaterThan(0, $this->foodMeal->id);
    }


    public function test_should_fail_with_empty_food(): void
    {
        $this->foodMeal->food_id = null;


        $this->assertFalse($this->foodMeal->isValid());


        $this->assertEquals(
            'Selecione um alimento!',
            $this->foodMeal->errors('food_id')
        );
    }


    public function test_should_fail_with_quantity_zero(): void
    {
        $this->foodMeal->quantity = 0;


        $this->assertFalse($this->foodMeal->isValid());


        $this->assertEquals(
            'A quantidade deve ser maior que zero!',
            $this->foodMeal->errors('quantity')
        );
    }


    public function test_meal_relationship(): void
    {
        $this->assertEquals(
            $this->meal->id,
            $this->foodMeal->meal->id
        );
    }


    public function test_food_relationship(): void
    {
        $this->assertEquals(
            $this->food->id,
            $this->foodMeal->food->id
        );
    }
}
