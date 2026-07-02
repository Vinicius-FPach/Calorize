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

    public function favoriteIndex(Request $request): void
    {
        $paginator = $this->current_user->foods()->paginate(
            page: $request->getParam('page', 1),
            per_page: 8,
            route: 'profile.favorite.paginate'
        );
        $foods = $paginator->registers();
        $this->render('profile/foods/favorite', compact('foods', 'paginator'));
    }

    public function new(): void
    {
        $food = $this->current_user->foods()->new();
        $title = 'Novo Alimento';
        $this->render('profile/foods/food', compact('food', 'title'));
    }

    public function create(Request $request): void
    {
        $params = $request->getParam('food');
        $food = $this->current_user->foods()->new($params);

        if (!empty($_FILES['food_image']['name'])) {
            $food->imageFile = $_FILES['food_image'];
        }

        if ($food->save()) {
            if (!empty($_FILES['food_image']['name'])) {
                $food->image()->upload($_FILES['food_image']);
            }

            FlashMessage::success('Alimento criado com sucesso!');
            $this->redirectTo(route('profile.foods.index'));
        } else {
            $title = 'Novo Alimento';
            $this->render('profile/foods/food', compact('food', 'title'));
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
        $this->render('profile/foods/show', compact('food'));
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
        $this->render('profile/foods/food', compact('food', 'title'));
    }

    public function update(Request $request): void
    {
        $uuid = $request->getParam('uuid');
        $params = $request->getParam('food');

        $food = $this->current_user->foods()->findBy(['uuid' => $uuid]);

        if (!$food) {
            FlashMessage::danger('Alimento não encontrado!');
            $this->redirectTo(route('profile.foods.index'));
            return;
        }

        $removeImage = ($request->getParam('remove_image') === '1') && ($food->photo_url != null);
        $oldPhotoUrl = $food->photo_url;

        $food->name     = $params['name'] ?? $food->name;
        $food->kcal     = $params['kcal'] ?? $food->kcal;
        $food->carbs    = $params['carbs'] ?? $food->carbs;
        $food->fats     = $params['fats'] ?? $food->fats;
        $food->protein  = $params['protein'] ?? $food->protein;
        $food->unit     = $params['unit'] ?? $food->unit;
        $food->category = $params['category'] ?? $food->category;

        $hasImageUpload = !empty($_FILES['food_image']['name']);

        if ($hasImageUpload) {
            $food->imageFile = $_FILES['food_image'];
        } elseif ($removeImage) {
            $food->photo_url = null;
        }

        if (!$food->hasChanges() && !$hasImageUpload && !$removeImage) {
            FlashMessage::warning('Nenhuma alteração detectada em relação aos dados atuais.');
            $this->redirectTo(route('profile.foods.index'));
            return;
        }

        if ($food->save()) {
            if ($hasImageUpload) {
                $food->image()->upload($_FILES['food_image']);
            } elseif ($removeImage) {
                $food->photo_url = $oldPhotoUrl;
                $food->image()->remove();
            }

            FlashMessage::success('Alimento atualizado com sucesso!');
            $this->redirectTo(route('profile.foods.index'));
        } else {
            $title = 'Editar Alimento';
            $this->render('profile/foods/food', compact('food', 'title'));
        }
    }

    public function destroy(Request $request): void
    {
        $params = $request->getParams();

        /** @var \App\Models\Food|null $food */
        $food = $this->current_user->foods()->findBy(['uuid' => $params['uuid']]);

        if (!$food) {
            FlashMessage::danger('Alimento não encontrado!');
            $this->redirectTo(route('profile.foods.index'));
            return;
        }

        if ($food->hasMeals()) {
            FlashMessage::danger('Não é possível remover um alimento que está vinculado a uma refeição!');
            $this->redirectTo(route('profile.foods.index'));
            return;
        }

        $food->image()->remove();
        $food->destroy();

        FlashMessage::success('Alimento removido com sucesso!');
        $this->redirectTo(route('profile.foods.index'));
    }

    public function favorite(Request $request): void
    {
        $uuid = $request->getParam('uuid');
        $params = $request->getParam('food');

        $food = $this->current_user->foods()->findBy(['uuid' => $uuid]);

        $fav = $food->favorite;

        if (!$fav) {
            $food->favorite = true;
            $food-save();
        }
        elseif($fav) {
            $food->favorite = false;
            $food-save();
        }
        $this->redirectTo(route('profile.foods.index'));
    }
}
