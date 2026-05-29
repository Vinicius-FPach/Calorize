<?php

namespace Tests\Integration\Access;

use GuzzleHttp\Client;
use Tests\TestCase;
use App\Models\User;
use App\Models\Profile;
use App\Models\Diet;

class DietAccessTest extends TestCase
{
    private Client $client;
    private User $user;

    public function setup(): void
    {
        parent::setUp();
        $this->client = new Client([
            'allow_redirects' => false,
            'base_uri' => 'http://web:8080'
        ]);
    }

    private function loginUser(bool $withProfile = true): string
    {
        $this->user = new User([
            'name' => 'Fulano',
            'email' => 'fulano@example.com',
            'password' => '123456',
            'password_confirmation' => '123456'
        ]);
        $this->user->save();

        if ($withProfile) {
            $profile = new Profile([
                'user_id'         => $this->user->id,
                'height'          => 175,
                'birthday'        => '1983-03-21',
                'weight'          => '70.00',
                'biotype'         => 'ECTOMORFO',
                'gender'          => 'M',
                'activity_factor' => '1.550',
                'objective'       => 'GANHAR',
            ]);
            $profile->save();
        }

        $loginResponse = $this->client->post('/login', [
            'form_params' => [
                'user' => [
                    'email' => 'fulano@example.com',
                    'password' => '123456'
                ]
            ]
        ]);

        return $loginResponse->getHeaderLine('Set-Cookie');
    }

    public function test_diets_index_should_not_be_accessible_without_authentication(): void
    {
        $response = $this->client->get('/diets');

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/login', $response->getHeaderLine('Location'));
    }

    public function test_diets_new_should_not_be_accessible_without_authentication(): void
    {
        $response = $this->client->get('/diets/new');

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/login', $response->getHeaderLine('Location'));
    }

    public function test_diets_new_should_redirect_to_biometric_without_profile(): void
    {
        $cookie = $this->loginUser(withProfile: false);

        $response = $this->client->get('/diets/new', [
            'headers' => ['Cookie' => $cookie]
        ]);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/profile/biometric/new', $response->getHeaderLine('Location'));
    }

    public function test_diets_edit_should_not_be_accessible_without_authentication(): void
    {
        $response = $this->client->get('/diets/1/edit');

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/login', $response->getHeaderLine('Location'));
    }

    public function test_diets_edit_should_redirect_when_diet_not_found(): void
    {
        $cookie = $this->loginUser();

        $response = $this->client->get('/diets/999/edit', [
            'headers' => ['Cookie' => $cookie]
        ]);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/diets', $response->getHeaderLine('Location'));
    }

    public function test_diets_show_should_redirect_when_diet_not_found(): void
    {
        $cookie = $this->loginUser();

        $response = $this->client->get('/diets/999', [
            'headers' => ['Cookie' => $cookie]
        ]);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/diets', $response->getHeaderLine('Location'));
    }

    public function test_diets_edit_should_not_be_accessible_by_another_user(): void
    {
        $cookie = $this->loginUser();

        $otherUser = new User([
            'name' => 'Outro',
            'email' => 'outro@example.com',
            'password' => '123456',
            'password_confirmation' => '123456'
        ]);
        $otherUser->save();

        $otherProfile = new Profile([
            'user_id'         => $otherUser->id,
            'height'          => 175,
            'birthday'        => '2000-05-15',
            'weight'          => '70.00',
            'biotype'         => 'ECTOMORFO',
            'gender'          => 'M',
            'activity_factor' => '1.550',
            'objective'       => 'GANHAR',
        ]);
        $otherProfile->save();

        $otherDiet = Diet::createFromProfile($otherUser, 'Dieta do Outro');
        $otherDiet->save();

        $response = $this->client->get('/diets/' . $otherDiet->id . '/edit', [
            'headers' => ['Cookie' => $cookie]
        ]);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/diets', $response->getHeaderLine('Location'));
    }
}
