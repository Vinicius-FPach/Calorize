<?php

namespace App\Controllers;

use Core\Http\Controllers\Controller;

class AdminController extends Controller
{
    protected string $layout = 'application';

    public function index(): void
    {
        $title = 'Admin';
        $this->render('admin/index', compact('title'));
    }

    public function destroy(Request $request): void
    {
        $user = User::findById($request->getParam('id'));
        
        foreach ($user->foods() as $food) {
            if (!$food->is_global) {
                $food->destroy();
            }
        }

        $user->destroy();

        FlashMessage::success('Usuário removido com sucesso!');
        $this->redirectTo(route('admin.index'));
    }
}
