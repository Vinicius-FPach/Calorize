<?php

namespace App\Models;

use Core\Database\ActiveRecord\BelongsTo;
use Lib\Validations;
use Core\Database\ActiveRecord\Model;
use App\Services\DietCalculator;
use DateTime;

/**
 * @property int $id
 * @property int $user_id
 * @property int $height
 * @property string $birthday
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
        'user_id', 'height', 'birthday', 'weight',
        'biotype', 'gender', 'activity_factor', 'objective'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function age(): int
    {
        $birthDate = DateTime::createFromFormat('Y-m-d', $this->birthday);
        $today = new DateTime('now', new \DateTimeZone('America/Sao_Paulo'));
        $age = $birthDate->diff($today);
        return (int) $age->y;
    }

    public function validates(): void
    {
        Validations::greaterThan('height', $this, 0, msg: 'A altura deve ser informada e maior que zero!');
        Validations::greaterThan('weight', $this, 0, msg: 'O peso deve ser informado e maior que zero!');

        Validations::maxValue('height', $this, 280, msg: 'A altura não pode ser maior que 280cm!');
        Validations::maxValue('weight', $this, 500, msg: 'O peso não pode ser maior que 500kg!');

        Validations::notEmpty('birthday', $this, msg: 'Informe sua data de nascimento!');
        Validations::notEmpty('gender', $this, msg: 'Selecione seu sexo!');
        Validations::notEmpty('biotype', $this, msg: 'Selecione seu biotipo!');
        Validations::notEmpty('objective', $this, msg: 'Selecione seu objetivo!');
        Validations::notEmpty('activity_factor', $this, msg: 'Selecione seu fator de atividade!');

        Validations::validateBirthday(
            'birthday',
            $this,
            15,
            invalidMsg: 'Informe uma data de nascimento válida!',
            futureMsg: 'A data de nascimento não pode ser futura!',
            msg: 'Você deve ter pelo menos 15 anos!'
        );

        Validations::inList(
            'activity_factor',
            $this,
            ['1.200', '1.375', '1.550', '1.725', '1.900'],
            msg: 'Fator de atividade inválido!'
        );
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
