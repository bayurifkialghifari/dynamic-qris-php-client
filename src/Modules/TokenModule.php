<?php

declare(strict_types=1);

namespace Bayurifkialghifari\DynamicQris\Modules;

use Bayurifkialghifari\DynamicQris\DTOs\RequestOptions;
use Bayurifkialghifari\DynamicQris\DTOs\TokenStatus;
use Bayurifkialghifari\DynamicQris\DTOs\TokenUpdate;
use Bayurifkialghifari\DynamicQris\DTOs\UpdateTokenRequest;
use Bayurifkialghifari\DynamicQris\DynamicQrisClient;
use Bayurifkialghifari\DynamicQris\Support\Validation;

final class TokenModule
{
    public function __construct(private readonly DynamicQrisClient $client) {}

    public function status(?string $provider = null, ?RequestOptions $options = null): TokenStatus
    {
        Validation::provider($provider);

        return TokenStatus::fromArray($this->client->request(
            'GET', '/token-status', array_filter(['provider' => $provider], fn ($value) => $value !== null), $options, 'status',
        ));
    }

    public function update(UpdateTokenRequest|array $request, ?RequestOptions $options = null): TokenUpdate
    {
        $request = is_array($request) ? UpdateTokenRequest::fromArray($request) : $request;

        return TokenUpdate::fromArray($this->client->request('POST', '/update-token', $request->toArray(), $options));
    }
}
