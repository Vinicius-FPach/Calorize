<?php

namespace Tests\Acceptance\Authentication;

use App\Models\User;
use Tests\Acceptance\BaseAcceptanceCest;
use Tests\Support\AcceptanceTester;

class LoginCest extends BaseAcceptanceCest
{
    public function tryToAcessRestrictedAreaWithoutAuthentication(AcceptanceTester $page): void
    {
        $page->amOnPage('/problems');
        $page->see('Você deve estar logado para acessar essa página');
        $page->seeInCurrentUrl('/login');
    }

    public function loginUnsuccessfully(AcceptanceTester $page): void
    {
        $page->amOnPage('/login');

        $page->fillField('user[email]', 'fulano@example.com');
        $page->fillField('user[password]', 'wrong_password');

        $page->click('Entrar');

        $page->see('Email e/ou senha inválidos!');
        $page->seeInCurrentUrl('/login');
    }

    public function loginSuccessfully(AcceptanceTester $page): void
    {
        $user = new User([
            'name' => 'User 1',
            'email' => 'fulano@example.com',
            'password' => '123456',
            'password_confirmation' => '123456'
        ]);
        $user->save();

        $page->amOnPage('/login');

        $page->fillField('user[email]', $user->email);
        $page->fillField('user[password]', $user->password);

        $page->click('Entrar');

        $page->see('Login realizado com sucesso!');
        $page->seeInCurrentUrl('/problems');
    }

    public function logout(AcceptanceTester $page): void
    {
        $user = new User([
            'name' => 'User 1',
            'email' => 'fulano@example.com',
            'password' => '123456',
            'password_confirmation' => '123456'
        ]);
        $user->save();

        $page->amOnPage('/login');
        $page->fillField('user[email]', $user->email);
        $page->fillField('user[password]', '123456');
        $page->click('Entrar');

        $page->click('summary');
        $page->click('Sair');

        $page->see('Logout realizado com sucesso!');
        $page->seeInCurrentUrl('/login');
    }

    public function loginSuccessfullyAsAdmin(AcceptanceTester $page): void
    {
        $admin = new User([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => '123456',
            'password_confirmation' => '123456',
            'is_admin' => 1
        ]);
        $admin->save();

        $page->amOnPage('/login');
        $page->fillField('user[email]', $admin->email);
        $page->fillField('user[password]', '123456');
        $page->click('Entrar');

        $page->see('Login realizado com sucesso!');
        $page->seeInCurrentUrl('/admin');
    }

    public function tryToAccessAdminAreaAsRegularUser(AcceptanceTester $page): void
    {
        $user = new User([
            'name' => 'User 1',
            'email' => 'fulano@example.com',
            'password' => '123456',
            'password_confirmation' => '123456'
        ]);
        $user->save();

        $page->amOnPage('/login');
        $page->fillField('user[email]', $user->email);
        $page->fillField('user[password]', '123456');
        $page->click('Entrar');

        $page->amOnPage('/admin');
        $page->see('Você não tem permissão para acessar essa página');
        $page->seeInCurrentUrl('/problems');
    }
}
