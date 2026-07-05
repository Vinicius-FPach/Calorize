<?php

namespace App\Controllers;

use Core\Http\Controllers\Controller;
use Core\Http\Request;
use App\Models\User;
use Lib\FlashMessage;
use App\Models\Food;

class AdminController extends Controller
{
    protected string $layout = 'application';

    public function index(Request $request): void
    {
        $status = $request->getParam('status', '');
        $foods = Food::allForAdmin($status);

        $this->render('admin/index', compact('foods', 'status'));
    }

    public function showFood(Request $request): void
    {
        $params = $request->getParams();
        $food = Food::findBy(['uuid' => $params['uuid']]);

        if (!$food) {
            FlashMessage::danger('Alimento não encontrado!');
            $this->redirectTo(route('admin.index'));
            return;
        }

        $title = $food->name;
        $this->render('admin/foods/show', compact('food'));
    }

    public function approveFood(Request $request): void
    {
        $params = $request->getParams();
        $food = Food::findBy(['uuid' => $params['uuid']]);

        if (!$food) {
            FlashMessage::danger('Alimento não encontrado!');
            $this->redirectTo(route('admin.index'));
            return;
        }

        $global = new Food([
            'user_id'           => null,
            'name'              => $food->name,
            'kcal'              => $food->kcal,
            'carbs'             => $food->carbs,
            'fats'              => $food->fats,
            'protein'           => $food->protein,
            'unit'              => $food->unit,
            'category'          => $food->category,
            'is_global'         => 1,
            'moderation_status' => 'APROVADO',
            'moderated_at'      => date('Y-m-d H:i:s'),
        ]);
        $global->save();

        $food->update([
            'moderation_status' => 'APROVADO',
            'moderated_at'      => date('Y-m-d H:i:s'),
        ]);

        FlashMessage::success('Alimento aprovado e adicionado aos alimentos globais!');
        $this->redirectTo(route('admin.foods.show', ['uuid' => $food->uuid]));
    }

    public function rejectFood(Request $request): void
    {
        $params = $request->getParams();
        $food = Food::findBy(['uuid' => $params['uuid']]);

        if (!$food) {
            FlashMessage::danger('Alimento não encontrado!');
            $this->redirectTo(route('admin.index'));
            return;
        }

        $food->update([
            'moderation_status' => 'REJEITADO',
            'moderated_at'      => date('Y-m-d H:i:s'),
        ]);

        FlashMessage::success('Alimento rejeitado!');
        $this->redirectTo(route('admin.foods.show', ['uuid' => $food->uuid]));
    }
}
