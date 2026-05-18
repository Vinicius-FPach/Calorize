<?php

namespace Tests\Unit\Models\Diets;

use App\Models\Diet;
use App\Models\Profile;
use App\Models\User;
use Tests\TestCase;

class DietTest extends TestCase
{
    private User $user;
    private Diet $diet;

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

        $profile = new Profile([
            'user_id'         => $this->user->id,
            'height'          => 175,
            'age'             => 25,
            'weight'          => '70.00',
            'biotype'         => 'ECTOMORFO',
            'gender'          => 'M',
            'activity_factor' => '1.550',
            'objective'       => 'GANHAR',
        ]);
        $profile->save();

        $this->diet = Diet::createFromProfile($this->user, 'Dieta Teste');
        $this->diet->save();
    }

    public function test_should_create_diet(): void
    {
        $this->assertGreaterThan(0, $this->diet->id);
    }

    public function test_is_active_should_be_true_by_default(): void
    {
        $diet = new Diet();
        $this->assertEquals(1, $diet->is_active);
    }

    public function test_should_fail_with_empty_name(): void
    {
        $this->diet->name = '';
        $this->assertFalse($this->diet->isValid());
        $this->assertEquals('O nome da dieta não pode ser vazio!', $this->diet->errors('name'));
    }

    public function test_should_fail_with_name_too_long(): void
    {
        $this->diet->name = 'Este nome de dieta possui mais de 32 caracteres';
        $this->assertFalse($this->diet->isValid());
        $this->assertEquals('O nome da dieta não pode ter mais de 32 caracteres!', $this->diet->errors('name'));
    }

    public function test_create_from_profile_should_set_nutritional_values(): void
    {
        $this->assertGreaterThan(0, $this->diet->basal_calc);
        $this->assertGreaterThan(0, $this->diet->get_calc);
        $this->assertGreaterThan(0, $this->diet->kcal_objt);
        $this->assertGreaterThan(0, $this->diet->protein);
        $this->assertGreaterThan(0, $this->diet->fat);
        $this->assertGreaterThan(0, $this->diet->carbs);
    }

    public function test_create_from_profile_should_set_user_id(): void
    {
        $this->assertEquals($this->user->id, $this->diet->user_id);
    }

    public function test_create_from_profile_should_set_name(): void
    {
        $this->assertEquals('Dieta Teste', $this->diet->name);
    }

    public function test_user_relationship(): void
    {
        $this->assertEquals($this->user->id, $this->diet->user->id);
    }
}
