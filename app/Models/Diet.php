<?php

namespace App\Models;

use Core\Database\ActiveRecord\BelongsTo;
use Lib\Validations;
use Core\Database\ActiveRecord\Model;
use App\Models\User;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property float $basal_calc
 * @property float $get_calc
 * @property float $kcal_objt
 * @property float $protein
 * @property float $fat
 * @property float $carbs
 * @property int $is_active
 * @property User $user
 */
class Diet extends Model
{
    protected static string $table = 'diets';
    protected static array $columns = [
        'user_id', 'name', 'basal_calc', 'get_calc',
        'kcal_objt', 'protein', 'fat', 'carbs', 'is_active'
    ];

    public function __construct($params = [])
    {
        parent::__construct($params);

        if ($this->is_active === null) {
            $this->is_active = 1;
        }
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function validates(): void
    {
        Validations::notEmpty('name', $this, msg: 'O nome da dieta não pode ser vazio!');
    }

    public static function createFromProfile(User $user, string $name): static
    {
        $profile = $user->profile();
        $calculator = $profile->calculator();

        return new static([
            'user_id'    => $user->id,
            'name'       => $name,
            'basal_calc' => $calculator->tmb(),
            'get_calc'   => $calculator->get(),
            'kcal_objt'  => $calculator->kcalGoal(),
            'protein'    => $calculator->protein(),
            'fat'        => $calculator->fat(),
            'carbs'      => $calculator->carbs(),
        ]);
    }
}
