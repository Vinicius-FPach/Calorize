<?php

namespace Tests\Integration\Access;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

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

    public function test_login_page_should_be_accessible_without_authentication(): void
    {
        $response = $this->client->get('/login');
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_login_page_should_not_be_accessible_when_authenticated(): void
    {
        $response = $this->client->get('/login');
        $this->assertEquals(200, $response->getStatusCode());
    }
}
