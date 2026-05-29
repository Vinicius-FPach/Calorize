<?php

namespace Tests\Acceptance\Diets;

use App\Models\User;
use App\Models\Profile;
use App\Models\Diet;
use Tests\Acceptance\BaseAcceptanceCest;
use Tests\Support\AcceptanceTester;

class DietCest extends BaseAcceptanceCest
{
    private function loginAndAccessDiets(AcceptanceTester $page): User
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

        $page->amOnPage('/diets');
        $page->seeInCurrentUrl('/diets');

        return $user;
    }

    private function loginWithoutProfile(AcceptanceTester $page): User
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

        return $user;
    }

    public function redirectToBiometricWhenNoProfile(AcceptanceTester $page): void
    {
        $this->loginWithoutProfile($page);

        $page->amOnPage('/diets/new');

        $page->seeInCurrentUrl('/profile/biometric/new');
        $page->see('Complete seu perfil biométrico antes de criar uma dieta!');
    }

    public function failToCreateWithEmptyName(AcceptanceTester $page): void
    {
        $this->loginAndAccessDiets($page);

        $page->amOnPage('/diets/new');
        $page->click('CRIAR DIETA');

        $page->seeInCurrentUrl('/diets');
        $page->see('O nome da dieta não pode ser vazio!');
    }

    public function failToCreateWithNameTooLong(AcceptanceTester $page): void
    {
        $this->loginAndAccessDiets($page);

        $page->amOnPage('/diets/new');
        $page->fillField('diet[name]', 'Este nome de dieta possui mais de 32 caracteres');
        $page->click('CRIAR DIETA');

        $page->seeInCurrentUrl('/diets');
        $page->see('O nome da dieta não pode ter mais de 32 caracteres!');
    }

    public function createDietSuccessfully(AcceptanceTester $page): void
    {
        $this->loginAndAccessDiets($page);

        $page->amOnPage('/diets/new');
        $page->fillField('diet[name]', 'Dieta Bulking');
        $page->click('CRIAR DIETA');

        $page->seeInCurrentUrl('/diets');
        $page->see('Dieta registrada com sucesso!');
        $page->see('Dieta Bulking');
    }

    public function failToUpdateWithEmptyName(AcceptanceTester $page): void
    {
        $user = $this->loginAndAccessDiets($page);

        $diet = Diet::createFromProfile($user, 'Dieta Teste');
        $diet->save();

        $page->amOnPage('/diets/' . $diet->id . '/edit');
        $page->fillField('diet[name]', '');
        $page->click('ATUALIZAR');

        $page->seeInCurrentUrl('/diets/' . $diet->id);
        $page->see('O nome da dieta não pode ser vazio!');
    }

    public function failToUpdateWithNameTooLong(AcceptanceTester $page): void
    {
        $user = $this->loginAndAccessDiets($page);

        $diet = Diet::createFromProfile($user, 'Dieta Teste');
        $diet->save();

        $page->amOnPage('/diets/' . $diet->id . '/edit');
        $page->fillField('diet[name]', 'Este nome de dieta possui mais de 32 caracteres');
        $page->click('ATUALIZAR');

        $page->seeInCurrentUrl('/diets/' . $diet->id);
        $page->see('O nome da dieta não pode ter mais de 32 caracteres!');
    }

    public function updateDietSuccessfully(AcceptanceTester $page): void
    {
        $user = $this->loginAndAccessDiets($page);

        $diet = Diet::createFromProfile($user, 'Dieta Teste');
        $diet->save();

        $page->amOnPage('/diets/' . $diet->id . '/edit');
        $page->fillField('diet[name]', 'Dieta Atualizada');
        $page->click('ATUALIZAR');

        $page->seeInCurrentUrl('/diets');
        $page->see('Dieta atualizada com sucesso!');
        $page->see('Dieta Atualizada');
    }

    public function warnWhenNoDataIsChanged(AcceptanceTester $page): void
    {
        $user = $this->loginAndAccessDiets($page);

        $diet = Diet::createFromProfile($user, 'Dieta Teste');
        $diet->save();

        $page->amOnPage('/diets/' . $diet->id . '/edit');
        $page->click('ATUALIZAR');

        $page->seeInCurrentUrl('/diets');
        $page->see('Nenhuma alteração detectada em relação aos dados atuais.');
    }

    public function listDiets(AcceptanceTester $page): void
    {
        $user = $this->loginAndAccessDiets($page);

        Diet::createFromProfile($user, 'Dieta 1')->save();
        Diet::createFromProfile($user, 'Dieta 2')->save();
        Diet::createFromProfile($user, 'Dieta 3')->save();

        $page->amOnPage('/diets');

        $page->see('Dieta 1');
        $page->see('Dieta 2');
        $page->see('Dieta 3');
    }

    public function destroyDiet(AcceptanceTester $page): void
    {
        $user = $this->loginAndAccessDiets($page);

        $diet = Diet::createFromProfile($user, 'Dieta Para Remover');
        $diet->save();

        $page->amOnPage('/diets');
        $page->see('Dieta Para Remover');

        $js = "document.getElementById('delete-{$diet->id}').classList.remove('hidden');" .
              "document.getElementById('delete-{$diet->id}').classList.add('flex');";
        $page->executeJS($js);
        $page->click('EXCLUIR');

        $page->seeInCurrentUrl('/diets');
        $page->see('Dieta removida com sucesso!');
        $page->dontSee('Dieta Para Remover');
    }

    public function paginateDiets(AcceptanceTester $page): void
    {
        $user = $this->loginAndAccessDiets($page);

        for ($i = 1; $i <= 9; $i++) {
            Diet::createFromProfile($user, 'Dieta ' . $i)->save();
        }

        $page->amOnPage('/diets');
        $page->see('Dieta 1');
        $page->dontSee('Dieta 9');

        $page->amOnPage('/diets/page/2');
        $page->see('Dieta 9');
    }
}
