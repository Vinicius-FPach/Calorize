<?php

namespace Tests\Acceptance\Profile;

use App\Models\User;
use App\Models\Profile;
use Tests\Acceptance\BaseAcceptanceCest;
use Tests\Support\AcceptanceTester;

class ProfileCest extends BaseAcceptanceCest
{
    private function loginAndAccessProfileWithoutBiometrics(AcceptanceTester $page): User
    {
        $user = new User([
            'name' => 'Novo Usuario',
            'email' => 'novo@example.com',
            'password' => '123456',
            'password_confirmation' => '123456'
        ]);
        $user->save();

        $page->amOnPage('/login');
        $page->fillField('user[email]', $user->email);
        $page->fillField('user[password]', '123456');
        $page->click('Entrar');

        $page->click('Perfil');

        $page->seeInCurrentUrl('/profile');

        $page->click('COMPLETAR PERFIL BIOMÉTRICO');

        $page->seeInCurrentUrl('/profile/biometric/new');

        return $user;
    }

    public function createProfileSuccessfully(AcceptanceTester $page): void
    {
        $this->loginAndAccessProfileWithoutBiometrics($page);

        $page->fillField('profile[height]', '175');
        $page->fillField('profile[weight]', '70.5');
        $page->fillField('profile[age]', '25');
        $page->selectOption('profile[gender]', 'M');
        $page->selectOption('profile[biotype]', 'MESOMORFO');
        $page->selectOption('profile[objective]', 'GANHAR');
        $page->selectOption('profile[activity_factor]', '1.200');

        $page->click('SALVAR');

        $page->seeInCurrentUrl('/profile');
        $page->see('Perfil biométrico criado com sucesso!');
    }

    public function failToCreateWithEmptyFields(AcceptanceTester $page): void
    {
        $this->loginAndAccessProfileWithoutBiometrics($page);

        $page->click('SALVAR');

        $page->seeInCurrentUrl('/profile/biometric');

        $page->see('A altura deve ser informada e maior que zero!');
        $page->see('Informe uma idade válida!');
        $page->see('O peso deve ser informado e maior que zero!');
        $page->see('Selecione seu sexo!');
        $page->see('Selecione seu biotipo!');
        $page->see('Selecione seu objetivo!');
        $page->see('Selecione seu fator de atividade!');
    }

    public function failWithMixedValuesAndRetainData(AcceptanceTester $page): void
    {
        $this->loginAndAccessProfileWithoutBiometrics($page);

        $page->fillField('profile[height]', '180');
        $page->fillField('profile[age]', '-5');
        $page->selectOption('profile[gender]', 'F');
        $page->selectOption('profile[activity_factor]', '1.550');

        $page->click('SALVAR');

        $page->see('Informe uma idade válida!');
        $page->see('O peso deve ser informado e maior que zero!');
        $page->see('Selecione seu biotipo!');
        $page->see('Selecione seu objetivo!');

        $page->seeInField('profile[height]', '180');
        $page->seeInField('profile[gender]', 'Feminino');
        $page->seeInField('profile[activity_factor]', 'Moderadamente ativo');
    }

    private function loginAndAccessProfileWithExistingBiometrics(AcceptanceTester $page): User
    {
        $user = new User([
            'name' => 'fulano',
            'email' => 'fulano@example.com',
            'password' => '123456',
            'password_confirmation' => '123456'
        ]);
        $user->save();

        $profile = new Profile([
            'user_id' => $user->id,
            'height' => 170,
            'weight' => 70.0,
            'age' => 25,
            'gender' => 'M',
            'biotype' => 'ECTOMORFO',
            'objective' => 'GANHAR',
            'activity_factor' => '1.200'
        ]);
        $profile->save();

        $page->amOnPage('/login');
        $page->fillField('user[email]', $user->email);
        $page->fillField('user[password]', '123456');
        $page->click('Entrar');

        $page->click('Perfil');

        $page->seeInCurrentUrl('/profile');

        $page->click('EDITAR PERFIL BIOMÉTRICO');

        $page->seeInCurrentUrl('/profile/biometric/edit');

        return $user;
    }

    public function updateProfileSuccessfully(AcceptanceTester $page): void
    {
        $this->loginAndAccessProfileWithExistingBiometrics($page);

        $page->fillField('profile[height]', '185');
        $page->fillField('profile[weight]', '82.5');
        $page->fillField('profile[age]', '23');
        $page->selectOption('profile[gender]', 'Masculino');
        $page->selectOption('profile[activity_factor]', '1.550');

        $page->click('ATUALIZAR');

        $page->seeInCurrentUrl('/profile');

        $page->see('Perfil biométrico atualizado com sucesso!');

        $page->click('EDITAR PERFIL BIOMÉTRICO');

        $page->seeInField('profile[height]', '185');
        $page->seeInField('profile[weight]', '82.50');
        $page->seeInField('profile[age]', '23');
        $page->seeInField('profile[gender]', 'Masculino');
        $page->seeInField('profile[activity_factor]', 'Moderadamente ativo');
    }

    public function failToUpdateWithNegativeValues(AcceptanceTester $page): void
    {
        $this->loginAndAccessProfileWithExistingBiometrics($page);

        $page->fillField('profile[age]', '-10');
        $page->fillField('profile[height]', '0');

        $page->click('ATUALIZAR');

        $page->seeInCurrentUrl('/profile/biometric');

        $page->see('Informe uma idade válida!');
        $page->see('A altura deve ser informada e maior que zero!');
    }

    public function warnWhenNoDataIsChanged(AcceptanceTester $page): void
    {
        $this->loginAndAccessProfileWithExistingBiometrics($page);

        $page->click('ATUALIZAR');

        $page->seeInCurrentUrl('/profile');

        $page->see('Nenhuma alteração detectada em relação aos dados atuais.');
    }

    public function failToUpdateWhenSelectsAreEmpty(AcceptanceTester $page): void
    {
        $this->loginAndAccessProfileWithExistingBiometrics($page);

        $page->selectOption('profile[gender]', '');
        $page->selectOption('profile[biotype]', '');

        $page->click('ATUALIZAR');

        $page->seeInCurrentUrl('/profile/biometric');

        $page->see('Selecione seu sexo!');
        $page->see('Selecione seu biotipo!');
    }

    public function failToCreateProfileWhenAlreadyExists(AcceptanceTester $page): void
    {
        $this->loginAndAccessProfileWithExistingBiometrics($page);

        $page->amOnPage('/profile/biometric/new');

        $page->seeInCurrentUrl('/profile/biometric/edit');
        $page->see('Você já possui um perfil biométrico!');
    }
}
