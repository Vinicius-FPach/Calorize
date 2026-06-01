<?php 

namespace Tests\Acceptance\Foods;

use App\Models\Food;
use App\Models\User;
use Tests\Acceptance\BaseAcceptanceCest;
use Tests\Support\AcceptanceTester;

class FoodCest extends BaseAcceptanceCest 
{

    private function loginAndAccessFoods(AcceptanceTester $page): User
    {
    $user = new User([
        'name' => 'Fulano',
        'email' => 'fulano@example.com',
        'password' => '123456',
        'password_confirmation' => '123456'
    ]);

    $user->save();

    $page->amOnPage('/login');
    $page->fillField('user[email]', $user->email);
    $page->fillField('user[password]', '123456');
    $page->click('Entrar');

    $page->amOnPage('/profile/foods');

    return $user;
    }

    private function createFood(User $user): Food
    {
    $food = new Food([
        'user_id' => $user->id,
        'name' => 'Frango',
        'kcal' => 165,
        'carbs' => 1,
        'fats' => 3.6,
        'protein' => 31,
        'unit' => 'g',
        'category' => 'Carnes'
    ]);

    $food->save();

    return $food;
}

    public function createFoodWithImage(AcceptanceTester $page)
{
    $this->loginAndAccessFoods($page);

    $page->click('NOVO ALIMENTO');

    $page->fillField('food[name]', 'Frango');
    $page->fillField('food[category]', 'Carnes');

    $page->selectOption('food[unit]', 'g');

    $page->fillField('food[kcal]', '165');
    $page->fillField('food[protein]', '31');
    $page->fillField('food[carbs]', '1');
    $page->fillField('food[fats]', '4');

    $page->attachFile('#food_image_input', 'avatar_test.jpg');

    $page->click('CRIAR ALIMENTO');

    $page->see('Frango');
    
}

public function showFoodImage(AcceptanceTester $I): void
{
    $this->loginAndAccessFoods($I);

    $I->click('NOVO ALIMENTO');

    $I->fillField('food[name]', 'Frango');
    $I->fillField('food[category]', 'Carnes');
    $I->selectOption('food[unit]', 'g');

    $I->fillField('food[kcal]', '165');
    $I->fillField('food[protein]', '31');
    $I->fillField('food[carbs]', '1');
    $I->fillField('food[fats]', '4');

    $I->attachFile('#food_image_input', 'avatar_test.jpg');
    $I->click('CRIAR ALIMENTO');

    $food = Food::findBy(['name' => 'Frango']);

    $I->amOnPage("/profile/foods/{$food->uuid}");

    $I->seeElement('img[src*="uploads/foods"]');
}
public function removeFoodImage(AcceptanceTester $I): void
{
    $this->loginAndAccessFoods($I);

    $I->click('NOVO ALIMENTO');

    $I->fillField('food[name]', 'Frango');
    $I->fillField('food[category]', 'Carnes');
    $I->selectOption('food[unit]', 'g');

    $I->fillField('food[kcal]', '165');
    $I->fillField('food[protein]', '31');
    $I->fillField('food[carbs]', '1');
    $I->fillField('food[fats]', '4');

    $I->attachFile('#food_image_input', 'avatar_test.jpg');

    $I->click('CRIAR ALIMENTO');

    $food = Food::findBy(['name' => 'Frango']);

    $I->amOnPage("/profile/foods/{$food->uuid}/edit");

    $I->see('Remover imagem atual');

    $I->checkOption('input[name="remove_image"]');

    $I->click('ATUALIZAR ALIMENTO');

    $I->dontSee('Remover imagem atual');
}
}