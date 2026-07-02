<?php

namespace App\Models;

use Core\Database\ActiveRecord\BelongsTo;
use Core\Database\ActiveRecord\HasMany;
use Core\Database\Database;
use Lib\Validations;
use Core\Database\ActiveRecord\Model;
use PDO;

/**
 * @property int $id
 * @property int $diet_id
 * @property string $name
 * @property Diet $diet
 */
class Meal extends Model
{
    protected static string $table = 'meals';
    protected static array $columns = ['diet_id', 'name'];

    public function diet(): BelongsTo
    {
        return $this->belongsTo(Diet::class, 'diet_id');
    }

    public function foodMeals(): HasMany
    {
        return $this->hasMany(FoodMeal::class, 'meal_id');
    }

    public function validates(): void
    {
        Validations::notEmpty('name', $this, msg: 'O nome da refeição não pode ser vazio!');
        Validations::maxLength('name', $this, 32, msg: 'O nome da refeição não pode ter mais de 32 caracteres!');
    }

    /**
     * @return array<int, array{food_meal_id: int, quantity: float, food: Food}>
     */
    public function items(): array
    {
        $sql = <<<SQL
            SELECT
                food_meal.id AS food_meal_id,
                food_meal.quantity AS quantity,
                foods.id, foods.uuid, foods.user_id, foods.name, foods.kcal,
                foods.carbs, foods.fats, foods.protein, foods.unit,
                foods.category, foods.is_global, foods.photo_url,
                foods.moderation_status, foods.moderated_at
            FROM food_meal
            INNER JOIN foods ON foods.id = food_meal.food_id
            WHERE food_meal.meal_id = :meal_id
        SQL;

        $pdo = Database::getDatabaseConn();
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':meal_id', $this->id);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $items = [];
        foreach ($rows as $row) {
            $foodMealId = (int) $row['food_meal_id'];
            $quantity = (float) $row['quantity'];

            unset($row['food_meal_id'], $row['quantity']);

            $items[] = [
                'food_meal_id' => $foodMealId,
                'quantity' => $quantity,
                'food' => new Food($row),
            ];
        }

        return $items;
    }

    /**
     * @return array<int, array{food_meal_id: int, favorite: int, food: Food}>
     */
    public function favorites(): array
    {
        $sql = <<<SQL
            SELECT
                food_meal.id AS food_meal_id,
                food_meal.favorite AS favorite,
                foods.id, foods.uuid, foods.user_id, foods.name, foods.kcal,
                foods.carbs, foods.fats, foods.protein, foods.unit,
                foods.category, foods.is_global, foods.photo_url,
                foods.moderation_status, foods.moderated_at
            FROM food_meal
            INNER JOIN foods ON foods.id = food_meal.food_id
            WHERE food_meal.meal_id = :meal_id AND food_meal.favorite = 1
        SQL;

        $pdo = Database::getDatabaseConn();
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':meal_id', $this->id);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $favorites = [];
        foreach ($rows as $row) {
            $foodMealId = (int) $row['food_meal_id'];
            $favorite = (int) $row['favorite'];

            unset($row['food_meal_id'], $row['favorite']);

            $favorites[] = [
                'food_meal_id' => $foodMealId,
                'favorite' => $favorite,
                'food' => new Food($row),
            ];
        }

        return $favorites;
    }

    /**
     * @return array{kcal: float, carbs: float, fats: float, protein: float}
     */
    public function totals(): array
    {
        $totals = ['kcal' => 0.0, 'carbs' => 0.0, 'fats' => 0.0, 'protein' => 0.0];

        foreach ($this->items() as $item) {
            $factor = $item['quantity'] / 100;
            $food = $item['food'];

            $totals['kcal']    += $food->kcal * $factor;
            $totals['carbs']   += $food->carbs * $factor;
            $totals['fats']    += $food->fats * $factor;
            $totals['protein'] += $food->protein * $factor;
        }

        return $totals;
    }
}
