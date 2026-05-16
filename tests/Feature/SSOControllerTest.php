<?php

namespace SengHeat\LaravelSso\Tests\Feature;

use Orchestra\Testbench\TestCase;
use SengHeat\LaravelSso\SSOServiceProvider;

class SSOControllerTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [SSOServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('sso.providers', [
            'google' => [
                'client_id'     => 'fake-client-id',
                'client_secret' => 'fake-client-secret',
                'redirect'      => '/sso/google/callback',
            ],
        ]);
    }

    /** @test */
    public function it_redirects_to_provider(): void
    {
        \Laravel\Socialite\Facades\Socialite::shouldReceive('driver->redirect')
            ->once()
            ->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));

        $response = $this->get('/sso/google/redirect');
        $response->assertRedirect();
    }

    /** @test */
    public function it_returns_404_for_disabled_provider(): void
    {
        $response = $this->get('/sso/notreal/redirect');
        $response->assertNotFound();
    }

    /** @test */
    public function it_redirects_with_error_on_callback_failure(): void
    {
        \Laravel\Socialite\Facades\Socialite::shouldReceive('driver->user')
            ->once()
            ->andThrow(new \Exception('Invalid state'));

        $response = $this->get('/sso/google/callback');
        $response->assertRedirect();
        $response->assertSessionHasErrors('sso');
    }
}
