<?php

namespace App\Models;

use Core\Database\ActiveRecord\BelongsTo;
use Lib\Validations;
use Core\Database\ActiveRecord\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property int $height
 * @property int $age
 * @property float $weight
 * @property string $biotype
 * @property string $gender
 * @property float $activity_factor
 * @property string $objective
 * @property User $user
 */
class Profile extends Model
{
    protected static string $table = 'profiles';
    protected static array $columns = ['user_id', 'height', 'age', 'weight', 'biotype', 'gender', 'activity_factor', 'objective'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function validates(): void
    {
        if ($this->height <= 0) {
            $this->addError('height', 'A altura deve ser informada e maior que zero!');
        }

        if ($this->age <= 0) {
            $this->addError('age', 'Informe uma idade válida!');
        }

        if ($this->weight <= 0) {
            $this->addError('weight', 'O peso deve ser informado e maior que zero!');
        }

        if ($this->gender === '') {
            $this->addError('gender', 'Selecione seu sexo!');
        }

        if ($this->biotype === '') {
            $this->addError('biotype', 'Selecione seu biotipo!');
        }

        if ($this->objective === '') {
            $this->addError('objective', 'Selecione seu objetivo!');
        }

        if ($this->activity_factor <= 0) {
            $this->addError('activity_factor', 'Selecione seu fator de atividade!');
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
}
