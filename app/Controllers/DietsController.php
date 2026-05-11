<?php

namespace App\Controllers;

use Core\Http\Controllers\Controller;
use Core\Http\Request;
use Lib\FlashMessage;

class DietsController extends Controller
{
    public function index(Request $request): void
    {
        $diets = $this->current_user->diets()->get();
        $title = 'Minhas Dietas';
        $this->render('diets/index', compact('diets', 'title'));
    }

    public function new(): void
    {
        if (!$this->current_user->profile()) {
            FlashMessage::danger('Complete seu perfil biométrico antes de criar uma dieta!');
            $this->redirectTo(route('profile.biometric.new'));
            return;
        }

        $diet = $this->current_user->diets()->new();
        $title = 'Nova Dieta';
        $this->render('diets/new', compact('diet', 'title'));
    }

    public function create(Request $request): void
    {
        if (!$this->current_user->profile()) {
            FlashMessage::danger('Complete seu perfil biométrico antes de criar uma dieta!');
            $this->redirectTo(route('profile.biometric.new'));
            return;
        }

        $params = $request->getParams();
        $diet = $this->current_user->diets()->new($params['diet']);

        if ($diet->save()) {
            FlashMessage::success('Dieta registrada com sucesso!');
            $this->redirectTo(route('diets.index'));
        } else {
            FlashMessage::danger('Existem dados incorretos! Por favor verifique!');
            $title = 'Nova Dieta';
            $this->render('diets/new', compact('diet', 'title'));
        }
    }

    public function destroy(Request $request): void
    {
        $params = $request->getParams();
        $diet = $this->current_user->diets()->findById($params['id']);
        $diet->destroy();

        FlashMessage::success('Dieta removida com sucesso!');
        $this->redirectTo(route('diets.index'));
    }
}
