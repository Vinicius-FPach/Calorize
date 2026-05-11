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
        // Validação de Altura
        // Verificamos se é menor ou igual a zero diretamente
        if ($this->height <= 0) {
            $this->addError('height', 'A altura deve ser informada e maior que zero!');
        }

        // Validação de Idade
        // Idade pode ser 0 (para bebês), mas no seu caso (Calorize), provavelmente > 0
        if ($this->age <= 0) {
            $this->addError('age', 'Informe uma idade válida!');
        }

        // Validação de Peso (float)
        if ($this->weight <= 0) {
            $this->addError('weight', 'O peso deve ser informado e maior que zero!');
        }

        // Para campos de texto (como Gênero ou Biotipo), o PHPStan aceita o empty()
        if ($this->gender === '') {
            $this->addError('gender', 'Selecione seu sexo!');
        }

        // Validação de Biotipo (Select)
        if ($this->biotype === '') {
            $this->addError('biotype', 'Selecione seu biotipo!');
        }

        // Validação de Objetivo (Select)
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
