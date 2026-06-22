<?php

namespace Database\Populate;

use App\Models\Diet;
use App\Models\Food;
use App\Models\FoodMeal;
use App\Models\Meal;
use App\Models\User;

class MealsPopulate
{
    public static function populate()
    {
        $admin = User::findBy(['email' => 'admin@example.com']);
        $fulano = User::findBy(['email' => 'fulano@example.com']);

        $adminDiet = Diet::findBy(['user_id' => $admin->id]);
        $fulanoDiet = Diet::findBy(['user_id' => $fulano->id]);

        $adminMeals = ['Café da Manhã', 'Almoço', 'Jantar'];
        foreach ($adminMeals as $name) {
            $meal = $adminDiet->meals()->new(['name' => $name]);
            $meal->save();
        }

        $fulanoMeals = ['Café da Manhã', 'Almoço', 'Lanche', 'Jantar'];
        foreach ($fulanoMeals as $name) {
            $meal = $fulanoDiet->meals()->new(['name' => $name]);
            $meal->save();
        }

        $adminFoods = Food::where(['user_id' => $admin->id]);
        $fulanoFoods = Food::where(['user_id' => $fulano->id]);

        $adminMealsList = $adminDiet->meals()->get();
        foreach ($adminMealsList as $meal) {
            $randomFoods = array_rand($adminFoods, 3);
            foreach ($randomFoods as $index) {
                $foodMeal = new FoodMeal([
                    'meal_id'  => $meal->id,
                    'food_id'  => $adminFoods[$index]->id,
                    'quantity' => rand(50, 150),
                ]);
                $foodMeal->save();
            }
        }

        $fulanoMealsList = $fulanoDiet->meals()->get();
        foreach ($fulanoMealsList as $meal) {
            $randomFoods = array_rand($fulanoFoods, 3);
            foreach ($randomFoods as $index) {
                $foodMeal = new FoodMeal([
                    'meal_id'  => $meal->id,
                    'food_id'  => $fulanoFoods[$index]->id,
                    'quantity' => rand(50, 150),
                ]);
                $foodMeal->save();
            }
        }

        echo "Meals populated successfully\n";
    }
}
