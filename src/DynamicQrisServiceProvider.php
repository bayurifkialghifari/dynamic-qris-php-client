<?php

declare(strict_types=1);

namespace Bayurifkialghifari\DynamicQris;

use Illuminate\Support\ServiceProvider;

final class DynamicQrisServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/dynamic-qris.php', 'dynamic-qris');
        $this->app->singleton(DynamicQrisClient::class, function ($app) {
            $config = $app['config']->get('dynamic-qris');

            return new DynamicQrisClient(
                baseUrl: $config['base_url'], apiKey: $config['api_key'],
                timeout: $config['timeout'], connectTimeout: $config['connect_timeout'],
            );
        });
        $this->app->alias(DynamicQrisClient::class, 'dynamic-qris');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__.'/../config/dynamic-qris.php' => config_path('dynamic-qris.php')], 'dynamic-qris-config');
        }
    }
}
