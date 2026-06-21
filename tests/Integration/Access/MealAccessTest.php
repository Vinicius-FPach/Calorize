<?php


namespace Tests\Integration\Access;


use GuzzleHttp\Client;
use Tests\TestCase;
use App\Models\User;
use App\Models\Profile;
use App\Models\Diet;
use App\Models\Meal;
use App\Models\Food;
use App\Models\FoodMeal;


class MealAccessTest extends TestCase
{
   private Client $client;


   public function setup(): void
   {
       parent::setup();


       $this->client = new Client([
           'base_uri' => 'http://web:8080',
           'allow_redirects' => false
       ]);
   }


   private function loginUser(): string
   {
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
           'weight' => '70.00',
           'biotype' => 'ECTOMORFO',
           'gender' => 'M',
           'activity_factor' => '1.550',
           'objective' => 'GANHAR'
       ]);
       $profile->save();


       $response = $this->client->post('/login', [
           'form_params' => [
               'user' => [
                   'email' => 'fulano@example.com',
                   'password' => '123456'
               ]
           ]
       ]);


       return $response->getHeaderLine('Set-Cookie');
   }


   public function test_meal_show_should_not_be_accessible_without_authentication(): void
   {
       $response = $this->client->get('/diets/1/meals/1');


       $this->assertEquals(302, $response->getStatusCode());
       $this->assertEquals('/login', $response->getHeaderLine('Location'));
   }


   public function test_meal_show_should_redirect_when_meal_not_found(): void
   {
       $cookie = $this->loginUser();


       $user = User::findByEmail('fulano@example.com');


       $diet = Diet::createFromProfile($user, 'Bulking');
       $diet->save();


       $response = $this->client->get("/diets/{$diet->id}/meals/999", [
           'headers' => [
               'Cookie' => $cookie
           ]
       ]);


       $this->assertEquals(302, $response->getStatusCode());
   }


   public function test_meal_show_should_not_be_accessible_by_another_user(): void
   {
       $cookie = $this->loginUser();


       $otherUser = new User([
           'name' => 'Outro',
           'email' => 'outro@example.com',
           'password' => '123456',
           'password_confirmation' => '123456'
       ]);
       $otherUser->save();


       $profile = new Profile([
           'user_id' => $otherUser->id,
           'height' => 175,
           'birthday' => '2000-05-15',
           'weight' => '70.00',
           'biotype' => 'ECTOMORFO',
           'gender' => 'M',
           'activity_factor' => '1.550',
           'objective' => 'GANHAR'
       ]);
       $profile->save();


       $diet = Diet::createFromProfile($otherUser, 'Dieta do Outro');
       $diet->save();


       $meal = new Meal([
           'diet_id' => $diet->id,
           'name' => 'Almoço'
       ]);
       $meal->save();


       $response = $this->client->get("/diets/{$diet->id}/meals/{$meal->id}", [
           'headers' => [
               'Cookie' => $cookie
           ]
       ]);


       $this->assertEquals(302, $response->getStatusCode());
       $this->assertEquals('/diets', $response->getHeaderLine('Location'));
   }


   public function test_food_meal_destroy_should_not_be_accessible_by_another_user(): void
   {
       $cookie = $this->loginUser();


       $otherUser = new User([
           'name' => 'Outro',
           'email' => 'outro@example.com',
           'password' => '123456',
           'password_confirmation' => '123456'
       ]);
       $otherUser->save();


       $profile = new Profile([
           'user_id' => $otherUser->id,
           'height' => 175,
           'birthday' => '2000-05-15',
           'weight' => '70.00',
           'biotype' => 'ECTOMORFO',
           'gender' => 'M',
           'activity_factor' => '1.550',
           'objective' => 'GANHAR'
       ]);
       $profile->save();


       $diet = Diet::createFromProfile($otherUser, 'Dieta');
       $diet->save();


       $meal = new Meal([
           'diet_id' => $diet->id,
           'name' => 'Almoço'
       ]);
       $meal->save();


       $food = new Food([
           'user_id' => $otherUser->id,
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


       $response = $this->client->delete("/food_meal/{$foodMeal->id}", [
           'headers' => [
               'Cookie' => $cookie
           ]
       ]);


       $this->assertEquals(302, $response->getStatusCode());
       $this->assertEquals('/diets', $response->getHeaderLine('Location'));


       $this->assertNotNull(FoodMeal::findById($foodMeal->id));
   }
}
