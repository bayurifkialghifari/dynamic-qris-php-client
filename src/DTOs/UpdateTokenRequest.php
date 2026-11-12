<?php

declare(strict_types=1);

namespace Bayurifkialghifari\DynamicQris\DTOs;

use Bayurifkialghifari\DynamicQris\Support\Validation;

final readonly class UpdateTokenRequest
{
    public function __construct(public string $token, public ?string $provider = null)
    {
        Validation::text($token, 'token');
        Validation::provider($provider, true);
    }

    public static function fromArray(array $data): self
    {
        Validation::text($data['token'] ?? null, 'token');
        Validation::provider($data['provider'] ?? null, true);

        return new self($data['token'], $data['provider'] ?? null);
    }

    public function toArray(): array
    {
        return array_filter(['token' => $this->token, 'provider' => $this->provider], fn ($value) => $value !== null);
    }
}
