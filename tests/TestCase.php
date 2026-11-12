<?php

declare(strict_types=1);

namespace Bayurifkialghifari\DynamicQris\Tests;

use Bayurifkialghifari\DynamicQris\DynamicQrisClient;
use Bayurifkialghifari\DynamicQris\DynamicQrisServiceProvider;
use Illuminate\Support\Facades\Http;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected array $fixture;

    protected function getPackageProviders($app): array
    {
        return [DynamicQrisServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('dynamic-qris.base_url', 'https://gateway.example/proxy/');
        $app['config']->set('dynamic-qris.api_key', 'test-key');
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixture = json_decode(file_get_contents(__DIR__.'/fixtures/gateway.json'), true, 512, JSON_THROW_ON_ERROR);
        Http::preventStrayRequests();
    }

    protected function client(): DynamicQrisClient
    {
        return $this->app->make(DynamicQrisClient::class);
    }

    protected function fake(string $key, int $status = 200): void
    {
        Http::fake(fn () => Http::response($this->fixture[$key], $status));
    }
}
