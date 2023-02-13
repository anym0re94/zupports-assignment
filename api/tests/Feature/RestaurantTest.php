<?php

namespace Tests\Feature;

use Tests\TestCase;

class RestaurantTest extends TestCase
{
    /**
     * Nearby restaurant search with default keyword test case.
     *
     * @return void
     */
    public function test_default_restaurant_request()
    {
        $response = $this->get('/api/restaurant?search=Bang sue');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'geometry',
                'name',
                'vicinity'
            ]
        ]);
    }

    /**
     * Nearby restaurant search with another valid keyword test case.
     *
     * @return void
     */
    public function test_valid_restaurant_request()
    {
        $response = $this->get('/api/restaurant?search=khonkaen');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'geometry',
                'name',
                'vicinity'
            ]
        ]);
    }

    /**
     * Nearby restaurant search with invalid (no place in google map) keyword test case.
     *
     * @return void
     */
    public function test_invalid_restaurant_request()
    {
        $response = $this->get('/api/restaurant?search=invalidinvalidinvalidinvalidinvalidinvalidinvalidinvalid');

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'error' => true
        ]);
    }

    /**
     * Nearby restaurant search without search keyword test case.
     *
     * @return void
     */
    public function test_no_keyword_restaurant_request()
    {
        $response = $this->get('/api/restaurant');

        $response->assertStatus(400);
        $response->assertJsonFragment([
            'error' => true
        ]);
    }

    /**
     * Nearby restaurant search with null keyword test case.
     *
     * @return void
     */
    public function test_null_keyword_restaurant_request()
    {
        $response = $this->get('/api/restaurant?search');

        $response->assertStatus(400);
        $response->assertJsonFragment([
            'error' => true
        ]);
    }

    /**
     * Nearby restaurant search with blank search keyword test case.
     *
     * @return void
     */
    public function test_blank_keyword_restaurant_request()
    {
        $response = $this->get('/api/restaurant?search=');

        $response->assertStatus(400);
        $response->assertJsonFragment([
            'error' => true
        ]);
    }
}
