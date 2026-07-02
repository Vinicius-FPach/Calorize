<?php

namespace App\Controllers;

use Core\Http\Controllers\Controller;
use Core\Http\Request;
use Lib\FlashMessage;
use App\Models\Food;
use App\Models\FoodMeal;
use App\Models\Diet;
use App\Models\Meal;

class MealsController extends Controller
{
    private function findDietOrRedirect(int $dietId): ?Diet
    {
        /** @var Diet|null $diet */
        $diet = $this->current_user->diets()->findById($dietId);

        if (!$diet) {
            FlashMessage::danger('Dieta não encontrada!');
            $this->redirectTo(route('diets.index'));
            return null;
        }

        return $diet;
    }

    public function new(Request $request): void
    {
        $params = $request->getParams();
        $diet = $this->findDietOrRedirect((int) $params['diet_id']);
        if (!$diet) {
            return;
        }

        $meal = $diet->meals()->new();
        $title = 'Nova Refeição';
        $this->render('diets/meals/new', compact('diet', 'meal', 'title'));
    }

    public function create(Request $request): void
    {
        $params = $request->getParams();
        $diet = $this->findDietOrRedirect((int) $params['diet_id']);
        if (!$diet) {
            return;
        }

        $meal = $diet->meals()->new([
            'name' => $params['meal']['name'] ?? null,
        ]);

        if ($meal->save()) {
            FlashMessage::success('Refeição criada com sucesso!');
            $this->redirectTo(route('diets.show', ['id' => $diet->id]));
        } else {
            $title = 'Nova Refeição';
            $this->render('diets/meals/new', compact('diet', 'meal', 'title'));
        }
    }

    public function show(Request $request): void
    {
        $params = $request->getParams();
        $diet = $this->findDietOrRedirect((int) $params['diet_id']);
        if (!$diet) {
            return;
        }

        /** @var Meal|null $meal */
        $meal = $diet->meals()->findById((int) $params['meal_id']);

        if (!$meal) {
            FlashMessage::danger('Refeição não encontrada!');
            $this->redirectTo(route('diets.show', ['id' => $diet->id]));
            return;
        }

        $search = $params['search'] ?? '';
        $availableFoods = Food::searchAvailable($this->current_user->id, $search);

        $items = $meal->items();
        $favorites = $meal->favorites();
        $totals = $meal->totals();
        $title = $meal->name;

        $this->render('diets/meals/show', compact(
            'diet',
            'meal',
            'items',
            'favorites',
            'totals',
            'availableFoods',
            'search',
            'title'
        ));
    }

    public function destroy(Request $request): void
    {
        $params = $request->getParams();
        $diet = $this->findDietOrRedirect((int) $params['diet_id']);
        if (!$diet) {
            return;
        }

        $meal = $diet->meals()->findById((int) $params['meal_id']);

        if (!$meal) {
            FlashMessage::danger('Refeição não encontrada!');
            $this->redirectTo(route('diets.show', ['id' => $diet->id]));
            return;
        }

        $meal->destroy();

        FlashMessage::success('Refeição removida com sucesso!');
        $this->redirectTo(route('diets.show', ['id' => $diet->id]));
    }

    public function addFood(Request $request): void
    {
        $params = $request->getParams();
        $diet = $this->findDietOrRedirect((int) $params['diet_id']);
        if (!$diet) {
            return;
        }

        $meal = $diet->meals()->findById((int) $params['meal_id']);

        if (!$meal) {
            FlashMessage::danger('Refeição não encontrada!');
            $this->redirectTo(route('diets.show', ['id' => $diet->id]));
            return;
        }

        $foodMeal = new FoodMeal([
            'meal_id'  => $meal->id,
            'food_id'  => $params['food_meal']['food_id'] ?? null,
            'quantity' => $params['food_meal']['quantity'] ?? null,
        ]);

        if ($foodMeal->save()) {
            FlashMessage::success('Alimento adicionado à refeição!');
        } else {
            FlashMessage::danger(
                $foodMeal->errors('quantity')
                    ?? $foodMeal->errors('food_id')
                    ?? 'Não foi possível adicionar o alimento.'
            );
        }

        $this->redirectTo(route('meals.show', ['diet_id' => $diet->id, 'meal_id' => $meal->id]));
    }

    public function addFavoriteFood(Request $request): void
    {
        $params = $request->getParams();
        $diet = $this->findDietOrRedirect((int) $params['diet_id']);
        if (!$diet) {
            return;
        }

        $meal = $diet->meals()->findById((int) $params['meal_id']);

        if (!$meal) {
            FlashMessage::danger('Refeição não encontrada!');
            $this->redirectTo(route('diets.show', ['id' => $diet->id]));
            return;
        }

        $foodMeal = new FoodMeal([
            'meal_id'  => $meal->id,
            'food_id'  => $params['food_meal']['food_id'] ?? null,
            'quantity' => $params['food_meal']['quantity'] ?? null,
            'favorite' => $params['food_meal']['favorite'] ?? 1,
        ]);

        if ($foodMeal->save()) {
            FlashMessage::success('Alimento favoritado!');
        } else {
            FlashMessage::danger(
                $foodMeal->errors('food_id')
                ?? $foodMeal->errors('meal_id')
                ?? 'Não foi possível adicionar o alimento.'
            );
        }

        $this->redirectTo(route('meals.show', ['diet_id' => $diet->id, 'meal_id' => $meal->id]));
    }

    public function removeFood(Request $request): void
    {
        $params = $request->getParams();
        $foodMeal = FoodMeal::findById((int) $params['food_meal_id']);

        if (!$foodMeal) {
            FlashMessage::danger('Item não encontrado!');
            $this->redirectTo(route('diets.index'));
            return;
        }

        $meal = $foodMeal->meal;
        $diet = $this->current_user->diets()->findById($meal->diet_id);

        if (!$diet) {
            FlashMessage::danger('Item não encontrado!');
            $this->redirectTo(route('diets.index'));
            return;
        }

        $foodMeal->destroy();

        FlashMessage::success('Alimento removido da refeição!');
        $this->redirectTo(route('meals.show', ['diet_id' => $diet->id, 'meal_id' => $meal->id]));
    }
}
