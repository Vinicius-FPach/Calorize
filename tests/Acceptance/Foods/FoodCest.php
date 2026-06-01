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

    public function createFoodWithoutImage(AcceptanceTester $page): void
    {
        $this->loginAndAccessFoods($page);

        $page->click('NOVO ALIMENTO');

        $page->fillField('food[name]', 'Arroz');
        $page->fillField('food[category]', 'Grãos');
        $page->selectOption('food[unit]', 'g');

        $page->fillField('food[kcal]', '130');
        $page->fillField('food[protein]', '2.5');
        $page->fillField('food[carbs]', '28');
        $page->fillField('food[fats]', '0.2');

        $page->click('CRIAR ALIMENTO');

        $page->seeCurrentUrlEquals('/profile/foods');
        $page->see('Arroz', 'h1');

        $page->seeElement('img[src*="food.svg"]');
    }

    public function failToCreateWithEmptyFields(AcceptanceTester $page): void
    {
        $this->loginAndAccessFoods($page);

        $page->click('NOVO ALIMENTO');
        $page->click('CRIAR ALIMENTO');

        $page->seeCurrentUrlEquals('/profile/foods');

        $page->see('O nome do alimento não pode ser vazio!');
        $page->see('Selecione a unidade de medida!');
        $page->see('Informe a categoria do alimento!');
    }

    public function failToCreateWithInvalidFileExtension(AcceptanceTester $page): void
    {
        $targetPath = codecept_data_dir('documento_falso.pdf');
        if (!file_exists($targetPath)) {
            file_put_contents($targetPath, '%PDF-1.4 ... Conteudo simulado de teste');
        }

        $this->loginAndAccessFoods($page);

        $page->click('NOVO ALIMENTO');

        $page->fillField('food[name]', 'Alimento Teste');
        $page->fillField('food[category]', 'Carnes');
        $page->selectOption('food[unit]', 'g');
        $page->fillField('food[kcal]', '100');

        $page->attachFile('#food_image_input', 'documento_falso.pdf');

        $page->click('CRIAR ALIMENTO');

        $page->seeCurrentUrlEquals('/profile/foods');

        $page->see('Apenas imagens JPG, JPEG e PNG são permitidas!');
    }

    public function createFoodWithImage(AcceptanceTester $page): void
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

        $page->attachFile('#food_image_input', 'food_test.jpg');

        $page->click('CRIAR ALIMENTO');

        $page->seeCurrentUrlEquals('/profile/foods');
        $page->see('Frango', 'h1');
        $page->see('Carnes', 'p');
        $page->see('165.00', '.text-primary');
        $page->see('31.00g');
        $page->see('1.00g');
        $page->see('4.00g');
        $page->seeElement('div img[alt="Frango"]');
    }

    public function showFoodImage(AcceptanceTester $page): void
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

        $page->attachFile('#food_image_input', 'food_test.jpg');
        $page->click('CRIAR ALIMENTO');

        $food = Food::findBy(['name' => 'Frango']);

        $page->amOnPage("/profile/foods/{$food->uuid}");

        $page->seeElement('img[src*="uploads/foods"]');
    }

    public function removeFoodImage(AcceptanceTester $page): void
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

        $page->attachFile('#food_image_input', 'food_test.jpg');
        $page->click('CRIAR ALIMENTO');

        $food = Food::findBy(['name' => 'Frango']);

        $page->amOnPage("/profile/foods/{$food->uuid}/edit");

        $page->click('Remover imagem');

        $page->click('ATUALIZAR ALIMENTO');

        $page->seeCurrentUrlEquals('/profile/foods');

        $page->dontSeeElement('img[src*="uploads/foods"]');
    }
}
