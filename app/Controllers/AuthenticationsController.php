<?php

namespace App\Controllers;

use App\Models\User;
use Core\Http\Controllers\Controller;
use Core\Http\Request;
use Lib\Authentication\Auth;
use Lib\FlashMessage;

class AuthenticationsController extends Controller
{
    protected string $layout = 'login';

    public function new(): void
    {
        if (Auth::check()) {
            $this->redirectTo(route('root'));
        }

        $this->render('authentications/new');
    }

    public function authenticate(Request $request): void
    {
        $params = $request->getParam('user');

        if (empty($params['email']) || empty($params['password'])) {
            FlashMessage::danger('Email e senha são obrigatórios!');
            $this->redirectTo(route('users.login'));
            return;
        }

        $user = User::findByEmail($params['email']);

        if ($user && $user->authenticate($params['password'])) {
            Auth::login($user);

            FlashMessage::success('Login realizado com sucesso!');

            if ($user->is_admin) {
                $this->redirectTo(route('admin.index'));
            } else {
                $this->redirectTo(route('diets.index'));
            }
        } else {
            FlashMessage::danger('Email e/ou senha inválidos!');
            $this->redirectTo(route('users.login'));
        }
    }

    public function destroy(): void
    {
        Auth::logout();
        FlashMessage::success('Logout realizado com sucesso!');
        $this->redirectTo(route('users.login'));
    }
}
