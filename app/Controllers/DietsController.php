<?php

namespace App\Controllers;

use Core\Http\Controllers\Controller;
use Core\Http\Request;
use Lib\FlashMessage;
use App\Models\Diet;

class DietsController extends Controller
{
    public function index(Request $request): void
    {
        $paginator = $this->current_user->diets()->paginate(page: $request->getParam('page', 1), per_page: 8);
        $diets = $paginator->registers();
        $this->render('diets/index', compact('diets', 'paginator'));
    }

    public function new(): void
    {
        $profile = $this->current_user->profile();

        if (!$profile) {
            FlashMessage::danger('Complete seu perfil biométrico antes de criar uma dieta!');
            $this->redirectTo(route('profile.biometric.new'));
            return;
        }

        $calculator = $profile->calculator();
        $kcalGoal = $calculator->kcalGoal();
        $protein  = $calculator->protein();
        $fat      = $calculator->fat();
        $carbs    = $calculator->carbs();

        $diet = $this->current_user->diets()->new();
        $title = 'Nova Dieta';
        $this->render('diets/new', compact('diet', 'title', 'kcalGoal', 'protein', 'fat', 'carbs'));
    }

    public function create(Request $request): void
    {
        $profile = $this->current_user->profile();

        if (!$profile) {
            FlashMessage::danger('Complete seu perfil biométrico antes de criar uma dieta!');
            $this->redirectTo(route('profile.biometric.new'));
            return;
        }

        $params = $request->getParams();
        $diet = Diet::createFromProfile($this->current_user, $params['diet']['name']);

        if ($diet->save()) {
            FlashMessage::success('Dieta registrada com sucesso!');
            $this->redirectTo(route('diets.index'));
        } else {
            $title = 'Nova Dieta';
            $kcalGoal = $diet->kcal_objt;
            $protein  = $diet->protein;
            $fat      = $diet->fat;
            $carbs    = $diet->carbs;
            $this->render('diets/new', compact('diet', 'title', 'kcalGoal', 'protein', 'fat', 'carbs'));
        }
    }

    public function show(Request $request): void
    {
        $params = $request->getParams();
        $diet = $this->current_user->diets()->findById($params['id']);

        if (!$diet) {
            FlashMessage::danger('Dieta não encontrada!');
            $this->redirectTo(route('diets.index'));
            return;
        }

        $title = $diet->name;
        $this->render('diets/show', compact('diet', 'title'));
    }

    public function edit(Request $request): void
    {
        $params = $request->getParams();
        $diet = $this->current_user->diets()->findById($params['id']);

        if (!$diet) {
            FlashMessage::danger('Dieta não encontrada!');
            $this->redirectTo(route('diets.index'));
            return;
        }

        $title = 'Editar Dieta';
        $this->render('diets/edit', compact('diet', 'title'));
    }

    public function update(Request $request): void
    {
        $id = $request->getParam('id');
        $params = $request->getParam('diet');

        $diet = $this->current_user->diets()->findById($id);

        if (!$diet) {
            FlashMessage::danger('Dieta não encontrada!');
            $this->redirectTo(route('diets.index'));
            return;
        }

        $diet->name = $params['name'] ?? $diet->name;

        if (!$diet->hasChanges()) {
            FlashMessage::warning('Nenhuma alteração detectada em relação aos dados atuais.');
            $this->redirectTo(route('diets.index'));
            return;
        }

        if ($diet->save()) {
            FlashMessage::success('Dieta atualizada com sucesso!');
            $this->redirectTo(route('diets.index'));
        } else {
            $title = 'Editar Dieta';
            $this->render('diets/edit', compact('diet', 'title'));
        }
    }

    public function destroy(Request $request): void
    {
        $params = $request->getParams();
        $diet = $this->current_user->diets()->findById($params['id']);

        if (!$diet) {
            FlashMessage::danger('Dieta não encontrada!');
            $this->redirectTo(route('diets.index'));
            return;
        }

        $diet->destroy();

        FlashMessage::success('Dieta removida com sucesso!');
        $this->redirectTo(route('diets.index'));
    }
}
