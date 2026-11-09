<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GithubPortfolioTest extends TestCase
{
    public function test_github_portfolio_orchestration_success(): void
    {
        Http::fake([
            'api.github.com/users/octocat/repos*' => Http::response([
                [
                    'name' => 'hello-world',
                    'language' => 'PHP',
                    'stargazers_count' => 42,
                    'html_url' => 'https://github.com/octocat/hello-world',
                ],
            ], 200),
            'api.github.com/users/octocat*' => Http::response([
                'login' => 'octocat',
                'name' => 'The Octocat',
                'bio' => 'Monalisa Octocat',
                'public_repos' => 8,
                'followers' => 9999,
                'html_url' => 'https://github.com/octocat',
            ], 200),
        ]);

        $response = $this->getJson('/api/v1/portfolios/github/octocat');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'github_login' => 'octocat',
                'display_name' => 'The Octocat',
            ],
        ]);
        $this->assertCount(1, $response->json('data.latest_repos'));
    }

    public function test_github_portfolio_user_not_found(): void
    {
        Http::fake([
            'api.github.com/users/non-existent-user*' => Http::response([], 404),
        ]);

        $response = $this->getJson('/api/v1/portfolios/github/non-existent-user');

        $response->assertStatus(404);
        $response->assertJson(['success' => false]);
    }
}
