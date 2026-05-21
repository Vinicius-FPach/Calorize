<?php

namespace Tests\Unit\Lib\Validations;

use PHPUnit\Framework\TestCase;
use Core\Database\ActiveRecord\Model;
use Lib\Validations;

class ValidationsTest extends TestCase
{
    public function test_not_empty(): void
    {
        $model = new class () extends Model {
            protected static array $columns = ['name'];
        };

        $this->assertFalse(Validations::notEmpty('name', $model));

        $model->name = 'Diego'; // @phpstan-ignore-line
        $this->assertTrue(Validations::notEmpty('name', $model));
    }

    public function test_password_confirmation(): void
    {
        $model = new class () extends Model {
            protected ?string $password = null;
            protected ?string $password_confirmation = null;
        };

        $model->password = '123456'; // @phpstan-ignore-line
        $model->password_confirmation = 'wrong'; // @phpstan-ignore-line

        $this->assertFalse(Validations::passwordConfirmation($model));

        $model->password_confirmation = '123456'; // @phpstan-ignore-line
        $this->assertTrue(Validations::passwordConfirmation($model));
    }

    public function test_greater_than(): void
    {
        $model = new class () extends Model {
            protected static array $columns = ['age'];
        };

        $model->age = 0; // @phpstan-ignore-line
        $this->assertFalse(Validations::greaterThan('age', $model, 0));

        $model->age = 1;
        $this->assertTrue(Validations::greaterThan('age', $model, 0));
    }

    public function test_max_length(): void
    {
        $model = new class () extends Model {
            protected static array $columns = ['name'];
        };

        $model->name = 'Nome muito longo demais'; // @phpstan-ignore-line
        $this->assertFalse(Validations::maxLength('name', $model, 10));

        $model->name = 'Nome';
        $this->assertTrue(Validations::maxLength('name', $model, 10));
    }

    public function test_max_value(): void
    {
        $model = new class () extends Model {
            protected static array $columns = ['age'];
        };

        $model->age = 121; // @phpstan-ignore-line
        $this->assertFalse(Validations::maxValue('age', $model, 120));

        $model->age = 120;
        $this->assertTrue(Validations::maxValue('age', $model, 120));
    }
}
