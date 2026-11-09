<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_token_on_valid_credentials(): void
    {
        $user = User::create([
            'name' => 'Pustakawan Del',
            'email' => 'pustakawan@del.ac.id',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'pustakawan@del.ac.id',
            'password' => 'password123',
            'device_name' => 'test-device',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'token_type',
                'access_token',
                'user' => ['id', 'name', 'email'],
            ],
        ]);
        $this->assertEquals('Bearer', $response->json('data.token_type'));
        $this->assertNotEmpty($response->json('data.access_token'));
    }

    public function test_login_returns_401_on_invalid_credentials(): void
    {
        User::create([
            'name' => 'Pustakawan Del',
            'email' => 'pustakawan@del.ac.id',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'pustakawan@del.ac.id',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401);
        $response->assertHeader('Content-Type', 'application/problem+json');
        $response->assertJson([
            'title' => 'Invalid Credentials',
            'status' => 401,
        ]);
    }

    public function test_me_and_logout_endpoints(): void
    {
        $user = User::create([
            'name' => 'Pustakawan Del',
            'email' => 'pustakawan@del.ac.id',
            'password' => Hash::make('password123'),
        ]);

        $token = $user->createToken('test')->plainTextToken;

        // Unauthenticated
        $this->getJson('/api/v1/auth/me')->assertStatus(401);

        // Authenticated
        $meRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/auth/me');
        $meRes->assertStatus(200);
        $this->assertEquals('pustakawan@del.ac.id', $meRes->json('data.email'));

        // Logout
        $logoutRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/auth/logout');
        $logoutRes->assertStatus(200);

        // Reset auth guards cache so the test runner re-authenticates token from DB
        auth()->forgetGuards();

        // Attempting to use the revoked token
        $meAfterLogout = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/auth/me');
        $meAfterLogout->assertStatus(401);
    }
}
