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
        if (is_null($this->height) || $this->height === '') {
            $this->addError('height', 'Informe sua altura!');
        } elseif ($this->height <= 0) {
            $this->addError('height', 'A altura não pode ser negativa!');
        }
        if (is_null($this->age) || $this->age === '') {
            $this->addError('age', 'Informe sua idade!');
        } elseif ($this->age < 0) {
            $this->addError('age', 'A idade não pode ser negativa!');
        }
        if (is_null($this->weight) || $this->weight === '') {
            $this->addError('weight', 'Informe seu peso!');
        } elseif ($this->weight <= 0 || $this->weight > 999) {
            $this->addError('weight', 'O peso deve ser maior que zero!');
        }
        if (is_null($this->biotype) || $this->biotype === '') {
            $this->addError('biotype', 'Selecione seu biotipo!');
        }
        if (is_null($this->gender) || $this->gender === '') {
            $this->addError('gender', 'Selecione seu sexo!');
        }
        if (is_null($this->activity_factor) || $this->activity_factor === '') {
            $this->addError('activity_factor', 'Selecione seu nível de atividade!');
        }
        if (is_null($this->objective) || $this->objective === '') {
            $this->addError('objective', 'Selecione seu objetivo!');
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