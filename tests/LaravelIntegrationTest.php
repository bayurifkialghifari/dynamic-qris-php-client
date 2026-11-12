<?php

declare(strict_types=1);

namespace Bayurifkialghifari\DynamicQris\Tests;

use Bayurifkialghifari\DynamicQris\DynamicQrisClient;
use Bayurifkialghifari\DynamicQris\DynamicQrisServiceProvider;
use Bayurifkialghifari\DynamicQris\Facades\DynamicQris;

final class LaravelIntegrationTest extends TestCase
{
    public function test_container_alias_facade_and_config_resolve_the_same_client(): void
    {
        $this->fake('account');
        $this->assertSame($this->client(), $this->app->make('dynamic-qris'));
        $this->assertSame($this->client(), DynamicQris::getFacadeRoot());
        $this->assertSame(10000, DynamicQris::account()->get()->balance);
        $this->assertSame(10, config('dynamic-qris.connect_timeout'));
        $this->assertSame($this->client(), $this->app->make(DynamicQrisClient::class));
    }

    public function test_config_publish_tag_registers_the_correct_file(): void
    {
        $paths = DynamicQrisServiceProvider::pathsToPublish(DynamicQrisServiceProvider::class, 'dynamic-qris-config');
        $this->assertCount(1, $paths);
        $this->assertSame(config_path('dynamic-qris.php'), array_values($paths)[0]);
        $this->assertFileExists(array_keys($paths)[0]);
    }
}
