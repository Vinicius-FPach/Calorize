<?php

namespace App\Models;

use Core\Database\ActiveRecord\BelongsTo;
use Lib\Validations;
use Core\Database\ActiveRecord\Model;
use App\Services\DietCalculator;

/**
 * @property int $id
 * @property int $user_id
 * @property int $height
 * @property int $age
 * @property float $weight
 * @property string $biotype
 * @property string $gender
 * @property string $activity_factor
 * @property string $objective
 * @property User $user
 */
class Profile extends Model
{
    protected static string $table = 'profiles';
    protected static array $columns = [
        'user_id', 'height', 'age', 'weight',
        'biotype', 'gender', 'activity_factor', 'objective'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function validates(): void
    {
        Validations::greaterThan('height', $this, 0, msg: 'A altura deve ser informada e maior que zero!');
        Validations::greaterThan('age', $this, 0, msg: 'Informe uma idade válida!');
        Validations::greaterThan('weight', $this, 0, msg: 'O peso deve ser informado e maior que zero!');
        Validations::greaterThan('activity_factor', $this, 0, msg: 'Selecione seu fator de atividade!');

        Validations::notEmpty('gender', $this, msg: 'Selecione seu sexo!');
        Validations::notEmpty('biotype', $this, msg: 'Selecione seu biotipo!');
        Validations::notEmpty('objective', $this, msg: 'Selecione seu objetivo!');

        $validFactors = ['1.200', '1.375', '1.550', '1.725', '1.900'];
        $isInvalid = !in_array(number_format((float)$this->activity_factor, 3, '.', ''), $validFactors);
        if (!$this->errors('activity_factor') && $isInvalid) {
                $this->addError('activity_factor', 'Fator de atividade inválido!');
        }
    }

    public function __set(string $property, mixed $value): void
    {
        if ($property === 'weight' && is_string($value) && $value !== '') {
            $value = str_replace(',', '.', $value);
            $value = number_format((float) $value, 2, '.', '');
        }

        parent::__set($property, $value);
    }

    public function calculator(): DietCalculator
    {
        return new DietCalculator($this);
    }
}
