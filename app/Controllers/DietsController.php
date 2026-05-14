<?php

namespace App\Controllers;

use Core\Http\Controllers\Controller;
use Core\Http\Request;
use Lib\FlashMessage;
use App\Services\DietCalculator;

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
        if (!$this->current_user->profile()) {
            FlashMessage::danger('Complete seu perfil biométrico antes de criar uma dieta!');
            $this->redirectTo(route('profile.biometric.new'));
            return;
        }

        $profile = $this->current_user->profile();
        $calculator = new DietCalculator($profile);

        $tmb = round($calculator->tmb(), 2);
        $get = round($calculator->get(), 2);
        $kcalGoal = round($calculator->kcalGoal(), 2);
        $protein = round($calculator->protein(), 2);
        $fat = round($calculator->fat(), 2);
        $carbs = round($calculator->carbs(), 2);

        $diet = $this->current_user->diets()->new();
        $title = 'Nova Dieta';
        $this->render('diets/new', compact('diet', 'title', 'tmb', 'get', 'kcalGoal', 'protein', 'fat', 'carbs'));
    }

    public function create(Request $request): void
    {
        if (!$this->current_user->profile()) {
            FlashMessage::danger('Complete seu perfil biométrico antes de criar uma dieta!');
            $this->redirectTo(route('profile.biometric.new'));
            return;
        }

        $profile = $this->current_user->profile();
        $calculator = new DietCalculator($profile);

        $tmb = round($calculator->tmb(), 2);
        $get = round($calculator->get(), 2);
        $kcalGoal = round($calculator->kcalGoal(), 2);
        $protein = round($calculator->protein(), 2);
        $fat = round($calculator->fat(), 2);
        $carbs = round($calculator->carbs(), 2);

        $params = $request->getParams();
        $params['diet']['user_id'] = $this->current_user->id;
        $params['diet']['basal_calc'] = $tmb;
        $params['diet']['get_calc'] = $get;
        $params['diet']['kcal_objt'] = $kcalGoal;
        $diet = $this->current_user->diets()->new($params['diet']);

        if ($diet->save()) {
            FlashMessage::success('Dieta registrada com sucesso!');
            $this->redirectTo(route('diets.index'));
        } else {
            $title = 'Nova Dieta';
            $this->render('diets/new', compact('diet', 'title', 'tmb', 'get', 'kcalGoal', 'protein', 'fat', 'carbs'));
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

        $profile = $this->current_user->profile();
        $calculator = new DietCalculator($profile);

        $kcalGoal = round($calculator->kcalGoal(), 2);
        $protein = round($calculator->protein(), 2);
        $fat = round($calculator->fat(), 2);
        $carbs = round($calculator->carbs(), 2);

        $title = $diet->name;
        $this->render('diets/show', compact('diet', 'title', 'kcalGoal', 'protein', 'fat', 'carbs'));
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

        $profile = $this->current_user->profile();
        $calculator = new DietCalculator($profile);

        $kcalGoal = round($calculator->kcalGoal(), 2);
        $protein = round($calculator->protein(), 2);
        $fat = round($calculator->fat(), 2);
        $carbs = round($calculator->carbs(), 2);

        $title = 'Editar Dieta';
        $this->render('diets/edit', compact('diet', 'title', 'kcalGoal', 'protein', 'fat', 'carbs'));
    }

    public function update(Request $request): void
    {
        $id = $request->getParam('id');
        $params = $request->getParam('diet');

        $diet = $this->current_user->diets()->findById($id);
        if ($diet->name === $params['name']) {
            FlashMessage::warning('Nenhuma alteração detectada em relação aos dados atuais.');
            $this->redirectTo(route('diets.index'));
            return;
        }

        $diet->name = $params['name'];

        if ($diet->save()) {
            FlashMessage::success('Dieta atualizada com sucesso!');
            $this->redirectTo(route('diets.index'));
        } else {
            FlashMessage::danger('Existem dados incorretos! Por favor verifique!');
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
