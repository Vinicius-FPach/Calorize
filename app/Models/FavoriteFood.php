<?php

namespace App\Models;

use Core\Database\ActiveRecord\BelongsTo;
use Core\Database\ActiveRecord\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property int $food_id
 */
class FavoriteFood extends Model
{
    protected static string $table = 'favorite_foods';

    protected static array $columns = [
        'user_id',
        'food_id'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function food(): BelongsTo
    {
        return $this->belongsTo(Food::class, 'food_id');
    }

    public function validates(): void
    {
    }

    public static function favorite(int $userId, int $foodId): void
    {
        if (self::isFavorite($userId, $foodId)) {
            return;
        }

        $favorite = new self([
            'user_id' => $userId,
            'food_id' => $foodId
        ]);

        $favorite->save();
    }

    public static function unfavorite(int $userId, int $foodId): void
    {
        $favorite = self::findBy([
            'user_id' => $userId,
            'food_id' => $foodId
        ]);

        if ($favorite) {
            $favorite->destroy();
        }
    }

    public static function isFavorite(int $userId, int $foodId): bool
    {
        return self::findBy([
            'user_id' => $userId,
            'food_id' => $foodId
        ]) !== null;
    }
}