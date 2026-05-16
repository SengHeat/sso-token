<?php

namespace SengHeat\LaravelSso\Tests\Unit;

use Orchestra\Testbench\TestCase;
use SengHeat\LaravelSso\Exceptions\ProviderNotConfiguredException;
use SengHeat\LaravelSso\SSOManager;
use SengHeat\LaravelSso\SSOServiceProvider;

class SSOManagerTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [SSOServiceProvider::class];
    }

    private function makeManager(array $config = []): SSOManager
    {
        return new SSOManager(array_merge([
            'providers' => [
                'google' => ['client_id' => 'abc', 'client_secret' => 'xyz', 'redirect' => '/'],
                'github' => ['client_id' => '',    'client_secret' => '',    'redirect' => '/'],
            ],
        ], $config));
    }

    /** @test */
    public function it_returns_only_enabled_providers(): void
    {
        $this->assertEquals(['google'], $this->makeManager()->enabledProviders());
    }

    /** @test */
    public function it_reports_enabled_state_correctly(): void
    {
        $manager = $this->makeManager();

        $this->assertTrue($manager->isEnabled('google'));
        $this->assertFalse($manager->isEnabled('github'));
        $this->assertFalse($manager->isEnabled('azure'));
    }

    /** @test */
    public function it_throws_for_unconfigured_provider(): void
    {
        $this->expectException(ProviderNotConfiguredException::class);
        $this->makeManager()->providerConfig('github');
    }

    /** @test */
    public function it_returns_correct_redirect_urls(): void
    {
        $manager = $this->makeManager([
            'redirect_after_login'  => '/home',
            'redirect_after_logout' => '/bye',
        ]);

        $this->assertEquals('/home', $manager->redirectAfterLogin());
        $this->assertEquals('/bye',  $manager->redirectAfterLogout());
    }
}
