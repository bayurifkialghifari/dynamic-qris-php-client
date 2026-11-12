<?php

declare(strict_types=1);

namespace Bayurifkialghifari\DynamicQris\Modules;

use Bayurifkialghifari\DynamicQris\DTOs\Health;
use Bayurifkialghifari\DynamicQris\DTOs\Readiness;
use Bayurifkialghifari\DynamicQris\DTOs\RequestOptions;
use Bayurifkialghifari\DynamicQris\DynamicQrisClient;

final class SystemModule
{
    public function __construct(private readonly DynamicQrisClient $client) {}

    public function health(?RequestOptions $options = null): Health
    {
        return Health::fromArray($this->client->request('GET', '/api/health', options: $options, mode: 'envelope', authenticated: false));
    }

    public function ready(?RequestOptions $options = null): Readiness
    {
        return Readiness::fromArray($this->client->request('GET', '/api/ready', options: $options, mode: 'envelope', authenticated: false));
    }

    /** @return list<mixed> */
    public function logs(?RequestOptions $options = null): array
    {
        return $this->client->request('GET', '/api/logs', options: $options)['logs'];
    }
}
