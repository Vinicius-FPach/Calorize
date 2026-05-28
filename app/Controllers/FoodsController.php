<?php

namespace App\Controllers;

use Core\Http\Controllers\Controller;
use Core\Http\Request;
use Lib\FlashMessage;
use App\Models\Food;
use App\Services\FoodImage;

class FoodsController extends Controller
{
    public function index(Request $request): void
    {
        $this->render('foods/index');
    }

    public function userIndex(Request $request): void
    {
        $paginator = $this->current_user->foods()->paginate(
            page: $request->getParam('page', 1),
            per_page: 8,
            route: 'profile.foods.paginate'
        );
        $foods = $paginator->registers();
        $this->render('profile/foods/index', compact('foods', 'paginator'));
    }

    public function new(): void
    {
        $food = $this->current_user->foods()->new();
        $title = 'Novo Alimento';
        $this->render('profile/foods/new', compact('food', 'title'));
    }

    public function create(Request $request): void
    {
        $params = $request->getParam('food');
        $food = $this->current_user->foods()->new($params);

        if (!empty($_FILES['food_image']['name'])) {
            $food->image()->validate($_FILES['food_image']);
        }

        if (!$food->hasErrors() && $food->save()) {
            if (!empty($_FILES['food_image']['name'])) {
                $food->image()->upload($_FILES['food_image']);
            }

            FlashMessage::success('Alimento criado com sucesso!');
            $this->redirectTo(route('profile.foods.index'));
        } else {
            $title = 'Novo Alimento';
            $this->render('profile/foods/new', compact('food', 'title'));
        }
    }

    public function show(Request $request): void
    {
        $params = $request->getParams();
        $food = $this->current_user->foods()->findBy(['uuid' => $params['uuid']]);

        if (!$food) {
            FlashMessage::danger('Alimento não encontrado!');
            $this->redirectTo(route('profile.foods.index'));
            return;
        }

        $title = $food->name;
        $this->render('profile/foods/show', compact('food', 'title'));
    }

    public function edit(Request $request): void
    {
        $params = $request->getParams();
        $food = $this->current_user->foods()->findBy(['uuid' => $params['uuid']]);

        if (!$food) {
            FlashMessage::danger('Alimento não encontrado!');
            $this->redirectTo(route('profile.foods.index'));
            return;
        }

        $title = 'Editar Alimento';
        $this->render('profile/foods/edit', compact('food', 'title'));
    }

    public function update(Request $request): void
    {
        $uuid = $request->getParam('uuid');
        $params = $request->getParam('food');

        $food = $this->current_user->foods()->findBy(['uuid' => $params['uuid']]);

        if (!$food) {
            FlashMessage::danger('Alimento não encontrado!');
            $this->redirectTo(route('profile.foods.index'));
            return;
        }

        $food->name     = $params['name'] ?? $food->name;
        $food->kcal     = $params['kcal'] ?? $food->kcal;
        $food->carbs    = $params['carbs'] ?? $food->carbs;
        $food->fats     = $params['fats'] ?? $food->fats;
        $food->protein  = $params['protein'] ?? $food->protein;
        $food->unit     = $params['unit'] ?? $food->unit;
        $food->category = $params['category'] ?? $food->category;

        if ($food->save()) {
            FlashMessage::success('Alimento atualizado com sucesso!');
            $this->redirectTo(route('profile.foods.index'));
        } else {
            $title = 'Editar Alimento';
            $this->render('profile/foods/edit', compact('food', 'title'));
        }
    }

    public function destroy(Request $request): void
    {
        $params = $request->getParams();
        $food = $this->current_user->foods()->findBy(['uuid' => $params['uuid']]);

        if (!$food) {
            FlashMessage::danger('Alimento não encontrado!');
            $this->redirectTo(route('profile.foods.index'));
            return;
        }

        $food->image()->remove();
        $food->destroy();

        FlashMessage::success('Alimento removido com sucesso!');
        $this->redirectTo(route('profile.foods.index'));
    }

    public function updateImage(Request $request): void
    {
        $params = $request->getParams();
        $food = $this->current_user->foods()->findBy(['uuid' => $params['uuid']]);

        if (!$food) {
            FlashMessage::danger('Alimento não encontrado!');
            $this->redirectTo(route('profile.foods.index'));
            return;
        }

        $food->image()->upload($_FILES['food_image'], [
            'extension' => ['jpg', 'jpeg', 'png'],
            'size' => 2 * 1024 * 1024
        ]);

        FlashMessage::success('Imagem atualizada com sucesso!');
        $this->redirectTo(route('profile.foods.edit', ['uuid' => $food->uuid]));
    }

    public function destroyImage(Request $request): void
    {
        $params = $request->getParams();
        $food = $this->current_user->foods()->findBy(['uuid' => $params['uuid']]);

        if (!$food) {
            FlashMessage::danger('Alimento não encontrado!');
            $this->redirectTo(route('profile.foods.index'));
            return;
        }

        $food->image()->remove();

        FlashMessage::success('Imagem removida com sucesso!');
        $this->redirectTo(route('profile.foods.edit', ['uuid' => $food->uuid]));
    }
}