<?php

namespace Tests\Integration\Access;

use GuzzleHttp\Client;
use Tests\TestCase;
use App\Models\User;

class AuthenticationAccessTest extends TestCase
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

    public function test_admin_route_should_not_be_accessible_without_authentication(): void
    {
        $response = $this->client->get('/admin');
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/login', $response->getHeaderLine('Location'));
    }

    public function test_logout_route_should_not_be_accessible_without_authentication(): void
    {
        $response = $this->client->get('/logout');
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/login', $response->getHeaderLine('Location'));
    }

    public function test_profile_route_should_not_be_accessible_without_authentication(): void
    {
        $response = $this->client->get('/profile');
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/login', $response->getHeaderLine('Location'));
    }

    public function test_profile_biometric_new_route_should_not_be_accessible_without_authentication(): void
    {
        $response = $this->client->get('/profile/biometric/new');
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/login', $response->getHeaderLine('Location'));
    }

    public function test_profile_biometric_create_route_should_not_be_accessible_without_authentication(): void
    {
        $response = $this->client->post('/profile/biometric');
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/login', $response->getHeaderLine('Location'));
    }

    public function test_profile_biometric_edit_route_should_not_be_accessible_without_authentication(): void
    {
        $response = $this->client->get('/profile/biometric/edit');
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/login', $response->getHeaderLine('Location'));
    }

    public function test_profile_biometric_update_route_should_not_be_accessible_without_authentication(): void
    {
        $response = $this->client->put('/profile/biometric');
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/login', $response->getHeaderLine('Location'));
    }

    public function test_login_page_should_not_be_accessible_when_authenticated(): void
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

        $response = $this->client->get('/login', [
            'headers' => ['Cookie' => $cookie]
        ]);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/', $response->getHeaderLine('Location'));
    }

    public function test_diets_index_route_should_not_be_accessible_without_authentication(): void
    {
        $response = $this->client->get('/diets');
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/login', $response->getHeaderLine('Location'));
    }

    public function test_diets_new_route_should_not_be_accessible_without_authentication(): void
    {
        $response = $this->client->get('/diets/new');
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/login', $response->getHeaderLine('Location'));
    }

    public function test_diets_create_route_should_not_be_accessible_without_authentication(): void
    {
        $response = $this->client->post('/diets');
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/login', $response->getHeaderLine('Location'));
    }

    public function test_diets_show_route_should_not_be_accessible_without_authentication(): void
    {
        $response = $this->client->get('/diets/1');
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/login', $response->getHeaderLine('Location'));
    }

    public function test_diets_edit_route_should_not_be_accessible_without_authentication(): void
    {
        $response = $this->client->get('/diets/1/edit');
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/login', $response->getHeaderLine('Location'));
    }

    public function test_diets_update_route_should_not_be_accessible_without_authentication(): void
    {
        $response = $this->client->put('/diets/1');
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/login', $response->getHeaderLine('Location'));
    }

    public function test_diets_destroy_route_should_not_be_accessible_without_authentication(): void
    {
        $response = $this->client->delete('/diets/1');
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/login', $response->getHeaderLine('Location'));
    }
}
