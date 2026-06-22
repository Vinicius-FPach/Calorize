<?php

namespace Tests\Acceptance\Meals;

use App\Models\User;
use App\Models\Profile;
use App\Models\Diet;
use App\Models\Meal;
use App\Models\Food;
use App\Models\FoodMeal;
use Tests\Acceptance\BaseAcceptanceCest;
use Tests\Support\AcceptanceTester;

class MealCest extends BaseAcceptanceCest
{
    private function loginUser(AcceptanceTester $page): User
    {
        $user = new User([
           'name' => 'Fulano',
           'email' => 'fulano@example.com',
           'password' => '123456',
           'password_confirmation' => '123456'
        ]);
        $user->save();


        $profile = new Profile([
           'user_id'         => $user->id,
           'height'          => 175,
           'birthday'        => '2000-05-15',
           'weight'          => '70.00',
           'biotype'         => 'ECTOMORFO',
           'gender'          => 'M',
           'activity_factor' => '1.550',
           'objective'       => 'GANHAR',
        ]);
        $profile->save();


        $page->amOnPage('/login');
        $page->fillField('user[email]', $user->email);
        $page->fillField('user[password]', '123456');
        $page->click('Entrar');


        return $user;
    }


    public function addFoodToMealSuccessfully(AcceptanceTester $page): void
    {
        $user = $this->loginUser($page);


        $diet = Diet::createFromProfile($user, 'Bulking');
        $diet->save();


        $meal = new Meal([
           'diet_id' => $diet->id,
           'name' => 'Almoço'
        ]);
        $meal->save();


        $food = new Food([
           'user_id' => $user->id,
           'name' => 'Banana',
           'kcal' => 89,
           'carbs' => 22,
           'fats' => 0.3,
           'protein' => 1.1,
           'unit' => 'g',
           'category' => 'Fruta'
        ]);
        $food->save();


        $page->amOnPage("/diets/{$diet->id}/meals/{$meal->id}");


        $page->fillField('food_meal[quantity]', '150');
        $page->click('.bi-plus');


        $page->see('Alimento adicionado à refeição!');
        $page->see('Banana');
        $page->see('150.00g');
    }


    public function listFoodsInsideMeal(AcceptanceTester $page): void
    {
        $user = $this->loginUser($page);


        $diet = Diet::createFromProfile($user, 'Bulking');
        $diet->save();


        $meal = new Meal([
           'diet_id' => $diet->id,
           'name' => 'Almoço'
        ]);
        $meal->save();


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
           'meal_id' => $meal->id,
           'food_id' => $banana->id,
           'quantity' => 150
        ]))->save();


        (new FoodMeal([
           'meal_id' => $meal->id,
           'food_id' => $arroz->id,
           'quantity' => 200
        ]))->save();


        $page->amOnPage("/diets/{$diet->id}/meals/{$meal->id}");


        $page->see('Banana');
        $page->see('Arroz');
        $page->see('150.00g');
        $page->see('200.00g');
    }


    public function removeFoodFromMeal(AcceptanceTester $page): void
    {
        $user = $this->loginUser($page);


        $diet = Diet::createFromProfile($user, 'Bulking');
        $diet->save();


        $meal = new Meal([
           'diet_id' => $diet->id,
           'name' => 'Almoço'
        ]);
        $meal->save();


        $food = new Food([
           'user_id' => $user->id,
           'name' => 'Banana',
           'kcal' => 89,
           'carbs' => 22,
           'fats' => 0.3,
           'protein' => 1.1,
           'unit' => 'g',
           'category' => 'Fruta'
        ]);
        $food->save();


        $foodMeal = new FoodMeal([
           'meal_id' => $meal->id,
           'food_id' => $food->id,
           'quantity' => 150
        ]);
        $foodMeal->save();


        $page->amOnPage("/diets/{$diet->id}/meals/{$meal->id}");


        $js =
           "document.getElementById('delete-item-{$foodMeal->id}').classList.remove('hidden');" .
           "document.getElementById('delete-item-{$foodMeal->id}').classList.add('flex');";


        $page->executeJS($js);


        $page->click('REMOVER');


        $page->see('Alimento removido da refeição!');
        $page->dontSee("Banana", ".meal-items");
    }
}
