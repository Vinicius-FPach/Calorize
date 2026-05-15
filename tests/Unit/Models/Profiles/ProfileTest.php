<?php

namespace Tests\Unit\Models\Profiles;

use App\Models\Profile;
use App\Models\User;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    private User $user;
    private Profile $profile;

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

        $this->profile = new Profile([
            'user_id' => $this->user->id,
            'height' => 170,
            'weight' => 70.0,
            'age' => 25,
            'gender' => 'M',
            'biotype' => 'ECTOMORFO',
            'objective' => 'GANHAR',
            'activity_factor' => '1.200'
        ]);
        $this->profile->save();
    }

    public function test_should_create_profile(): void
    {
        $this->assertGreaterThan(0, $this->profile->id);
    }

    public function test_weight_should_convert_comma_to_dot(): void
    {
        $profile = new Profile(['weight' => '70,5']);
        $this->assertEquals('70.50', $profile->weight);
    }

    public function test_weight_should_keep_two_decimal_places(): void
    {
        $profile = new Profile(['weight' => '70.555']);
        $this->assertEquals('70.56', $profile->weight);
    }

    public function test_should_fail_with_invalid_height(): void
    {
        $this->profile->height = 0;
        $this->assertFalse($this->profile->isValid());
        $this->assertEquals('A altura deve ser informada e maior que zero!', $this->profile->errors('height'));
    }

    public function test_should_fail_with_invalid_age(): void
    {
        $this->profile->age = -1;
        $this->assertFalse($this->profile->isValid());
        $this->assertEquals('Informe uma idade válida!', $this->profile->errors('age'));
    }

    public function test_should_fail_with_invalid_weight(): void
    {
        $this->profile->weight = 0;
        $this->assertFalse($this->profile->isValid());
        $this->assertEquals('O peso deve ser informado e maior que zero!', $this->profile->errors('weight'));
    }

    public function test_should_fail_with_empty_gender(): void
    {
        $this->profile->gender = '';
        $this->assertFalse($this->profile->isValid());
        $this->assertEquals('Selecione seu sexo!', $this->profile->errors('gender'));
    }

    public function test_should_fail_with_empty_biotype(): void
    {
        $this->profile->biotype = '';
        $this->assertFalse($this->profile->isValid());
        $this->assertEquals('Selecione seu biotipo!', $this->profile->errors('biotype'));
    }

    public function test_should_fail_with_empty_objective(): void
    {
        $this->profile->objective = '';
        $this->assertFalse($this->profile->isValid());
        $this->assertEquals('Selecione seu objetivo!', $this->profile->errors('objective'));
    }

    public function test_should_fail_with_invalid_activity_factor(): void
    {
        $this->profile->activity_factor = '9.999';
        $this->assertFalse($this->profile->isValid());
        $this->assertEquals('Fator de atividade inválido!', $this->profile->errors('activity_factor'));
    }

    public function test_user_relationship(): void
    {
        $this->assertEquals($this->user->id, $this->profile->user->id);
    }
}
