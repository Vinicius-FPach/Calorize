<?php

namespace App\Models;

use Core\Database\ActiveRecord\BelongsTo;
use Lib\Validations;
use Core\Database\ActiveRecord\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property float $basal_calc
 * @property float $get_calc
 * @property float $kcal_objt
 * @property int $is_active
 * @property User $user
 */
class Diet extends Model
{
    protected static string $table = 'diets';
    protected static array $columns = ['user_id', 'name', 'basal_calc', 'get_calc', 'kcal_objt', 'is_active'];

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
        Validations::notEmpty('name', $this);
    }
}
