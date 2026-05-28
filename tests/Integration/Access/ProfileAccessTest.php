<?php

namespace Tests\Integration\Access;

use GuzzleHttp\Client;
use Tests\TestCase;
use App\Models\User;
use App\Models\Profile;

class ProfileAccessTest extends TestCase
{
    private Client $client;

    public function setup(): void
    {
        parent::setUp();
        $this->client = new Client([
            'allow_redirects' => false,
            'base_uri' => 'http://web:8080'
        ]);
    }

    public function test_biometric_edit_should_not_be_accessible_without_profile(): void
    {
        $user = new User([
            'name' => 'Fulano',
            'email' => 'fulano@example.com',
            'password' => '123456',
            'password_confirmation' => '123456'
        ]);
        $user->save();

        $loginResponse = $this->client->post('/login', [
            'form_params' => [
                'user' => [
                    'email' => 'fulano@example.com',
                    'password' => '123456'
                ]
            ]
        ]);

        $cookie = $loginResponse->getHeaderLine('Set-Cookie');

        $response = $this->client->get('/profile/biometric/edit', [
            'headers' => ['Cookie' => $cookie]
        ]);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/profile/biometric/new', $response->getHeaderLine('Location'));
    }

    public function test_biometric_new_should_not_be_accessible_with_existing_profile(): void
    {
        $user = new User([
            'name' => 'Fulano',
            'email' => 'fulano@example.com',
            'password' => '123456',
            'password_confirmation' => '123456'
        ]);
        $user->save();

        $profile = new Profile([
            'user_id' => $user->id,
            'height' => 170,
            'weight' => 70.0,
            'birthday' => '2000-05-15',
            'gender' => 'M',
            'biotype' => 'ECTOMORFO',
            'objective' => 'GANHAR',
            'activity_factor' => '1.550'
        ]);
        $profile->save();

        $loginResponse = $this->client->post('/login', [
            'form_params' => [
                'user' => [
                    'email' => 'fulano@example.com',
                    'password' => '123456'
                ]
            ]
        ]);

        $cookie = $loginResponse->getHeaderLine('Set-Cookie');

        $response = $this->client->get('/profile/biometric/new', [
            'headers' => ['Cookie' => $cookie]
        ]);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/profile/biometric/edit', $response->getHeaderLine('Location'));
    }
}
