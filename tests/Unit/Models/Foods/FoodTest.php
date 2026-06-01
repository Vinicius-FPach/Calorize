<?php

namespace Tests\Unit\Models\Foods;

use App\Models\Food;
use App\Models\User;
use Tests\TestCase;

class FoodTest extends TestCase
{
    private User $user;
    private Food $food;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = new User([
            'name' => 'Fulano',
            'email' => 'fulano@example.com',
            'password' => '123456',
            'password_confirmation' => '123456'
        ]);
        $this->user->save();

        $this->food = new Food([
            'user_id' => $this->user->id,
            'name' => 'Banana',
            'kcal' => 89,
            'carbs' => 22,
            'fats' => 0.3,
            'protein' => 1.1,
            'unit' => 'g',
            'category' => 'Fruta'
        ]);

        $this->food->save();
    }

    public function test_should_create_food(): void
    {
        $this->assertGreaterThan(0, $this->food->id);
    }

    public function test_should_generate_uuid(): void
    {
        $this->assertNotEmpty($this->food->uuid);
        $this->assertEquals(16, strlen($this->food->uuid));
    }

    public function test_should_set_default_values(): void
    {
        $food = new Food();

        $this->assertEquals(0, $food->is_global);
        $this->assertEquals('PENDENTE', $food->moderation_status);
    }

    public function test_should_fail_with_empty_name(): void
    {
        $this->food->name = '';

        $this->assertFalse($this->food->isValid());

        $this->assertEquals(
            'O nome do alimento não pode ser vazio!',
            $this->food->errors('name')
        );
    }

    public function test_should_fail_with_empty_unit(): void
    {
        $this->food->unit = '';

        $this->assertFalse($this->food->isValid());

        $this->assertEquals(
            'Selecione a unidade de medida!',
            $this->food->errors('unit')
        );
    }

    public function test_should_fail_with_empty_category(): void
    {
        $this->food->category = '';

        $this->assertFalse($this->food->isValid());

        $this->assertEquals(
            'Informe a categoria do alimento!',
            $this->food->errors('category')
        );
    }

    public function test_should_fail_with_invalid_kcal(): void
    {
        $this->food->kcal = 0;

        $this->assertFalse($this->food->isValid());

        $this->assertEquals(
            'O valor calórico deve ser maior que zero!',
            $this->food->errors('kcal')
        );
    }

    public function test_should_fail_when_kcal_exceeds_limit(): void
    {
        $this->food->kcal = 901;

        $this->assertFalse($this->food->isValid());

        $this->assertEquals(
            'O valor calórico não pode passar de 900 kcal a cada 100g/mL!',
            $this->food->errors('kcal')
        );
    }

    public function test_should_fail_when_carbs_exceeds_limit(): void
    {
        $this->food->carbs = 101;

        $this->assertFalse($this->food->isValid());

        $this->assertEquals(
            'Os carboidratos não podem passar de 100g a cada 100g/mL!',
            $this->food->errors('carbs')
        );
    }

    public function test_should_fail_when_fats_exceeds_limit(): void
    {
        $this->food->fats = 101;

        $this->assertFalse($this->food->isValid());

        $this->assertEquals(
            'As gorduras não podem passar de 100g a cada 100g/mL!',
            $this->food->errors('fats')
        );
    }

    public function test_should_fail_when_protein_exceeds_limit(): void
    {
        $this->food->protein = 101;

        $this->assertFalse($this->food->isValid());

        $this->assertEquals(
            'As proteínas não podem passar de 100g a cada 100g/mL!',
            $this->food->errors('protein')
        );
    }

    public function test_user_relationship(): void
    {
        $this->assertEquals(
            $this->user->id,
            $this->food->user->id
        );
    }

    public function test_should_fail_with_invalid_image_extension(): void
    {
        $this->food->imageFile = [
            'name' => 'arquivo.pdf',
            'size' => 1000
        ];

        $this->assertFalse($this->food->isValid());

        $this->assertEquals(
            'Apenas imagens JPG, JPEG e PNG são permitidas!',
            $this->food->errors('imageFile')
        );
    }

    public function test_should_fail_with_large_image(): void
    {
        $this->food->imageFile = [
            'name' => 'foto.jpg',
            'size' => 6 * 1024 * 1024
        ];

        $this->assertFalse($this->food->isValid());

        $this->assertEquals(
            'A imagem não pode ser maior que 5MB!',
            $this->food->errors('imageFile')
        );
    }
}