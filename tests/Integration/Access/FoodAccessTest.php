<?php

namespace Tests\Integration\Access;

use GuzzleHttp\Client;
use Tests\TestCase;

class FoodAccessTest extends TestCase
{
    private Client $client;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client([
            'allow_redirects' => false,
            'base_uri' => 'http://web:8080'
        ]);
    }

    public function test_profile_foods_index_route_should_not_be_accessible_without_authentication(): void
    {
        $response = $this->client->get('/profile/foods');
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/login', $response->getHeaderLine('Location'));
    }

    public function test_profile_foods_paginate_route_should_not_be_accessible_without_authentication(): void
    {
        $response = $this->client->get('/profile/foods/page/1');
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/login', $response->getHeaderLine('Location'));
    }

    public function test_profile_foods_new_route_should_not_be_accessible_without_authentication(): void
    {
        $response = $this->client->get('/profile/foods/new');
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/login', $response->getHeaderLine('Location'));
    }

    public function test_profile_foods_create_route_should_not_be_accessible_without_authentication(): void
    {
        $response = $this->client->post('/profile/foods');
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/login', $response->getHeaderLine('Location'));
    }

    public function test_profile_foods_show_route_should_not_be_accessible_without_authentication(): void
    {
        $response = $this->client->get('/profile/foods/any-uuid-123');
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/login', $response->getHeaderLine('Location'));
    }

    public function test_profile_foods_edit_route_should_not_be_accessible_without_authentication(): void
    {
        $response = $this->client->get('/profile/foods/any-uuid-123/edit');
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/login', $response->getHeaderLine('Location'));
    }

    public function test_profile_foods_update_route_should_not_be_accessible_without_authentication(): void
    {
        $response = $this->client->put('/profile/foods/any-uuid-123');
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/login', $response->getHeaderLine('Location'));
    }

    public function test_profile_foods_destroy_route_should_not_be_accessible_without_authentication(): void
    {
        $response = $this->client->delete('/profile/foods/any-uuid-123');
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/login', $response->getHeaderLine('Location'));
    }
}
