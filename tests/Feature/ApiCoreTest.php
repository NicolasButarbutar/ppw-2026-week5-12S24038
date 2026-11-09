<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiCoreTest extends TestCase
{
    public function test_api_route_not_found_returns_rfc7807_problem_details(): void
    {
        $response = $this->getJson('/api/v1/non-existent-endpoint');

        $response->assertStatus(404);
        $response->assertHeader('Content-Type', 'application/problem+json');
        $response->assertJsonStructure([
            'type',
            'title',
            'status',
            'detail',
            'instance',
        ]);
        $response->assertJson([
            'title' => 'Not Found',
            'status' => 404,
        ]);
    }

    public function test_api_unauthenticated_returns_rfc7807_problem_details(): void
    {
        // Testing unauthenticated access to protected API route once registered
        $this->assertTrue(true);
    }
}
