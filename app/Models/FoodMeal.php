<?php

namespace App\Models;

use Core\Database\ActiveRecord\BelongsTo;
use Lib\Validations;
use Core\Database\ActiveRecord\Model;

/**
 * @property int $id
 * @property int $meal_id
 * @property int $food_id
 * @property float $quantity
 * @property Meal $meal
 * @property Food $food
 */
class FoodMeal extends Model
{
    protected static string $table = 'food_meal';
    protected static array $columns = ['meal_id', 'food_id', 'quantity'];

    public function meal(): BelongsTo
    {
        return $this->belongsTo(Meal::class, 'meal_id');
    }

    public function food(): BelongsTo
    {
        return $this->belongsTo(Food::class, 'food_id');
    }

    public function validates(): void
    {
        Validations::notEmpty('food_id', $this, msg: 'Selecione um alimento!');
        Validations::greaterThan('quantity', $this, 0, msg: 'A quantidade deve ser maior que zero!');
    }
}
