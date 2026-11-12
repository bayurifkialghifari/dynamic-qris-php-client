<?php

declare(strict_types=1);

namespace Bayurifkialghifari\DynamicQris\DTOs;

use Bayurifkialghifari\DynamicQris\Support\Validation;

final readonly class CreateQrisRequest
{
    public function __construct(public int $amount, public ?string $provider = null)
    {
        Validation::integer($amount, 'amount');
        Validation::provider($provider);
    }

    public static function fromArray(array $data): self
    {
        Validation::integer($data['amount'] ?? null, 'amount');
        Validation::provider($data['provider'] ?? null);

        return new self($data['amount'], $data['provider'] ?? null);
    }

    public function toArray(): array
    {
        return array_filter(['amount' => $this->amount, 'provider' => $this->provider], fn ($value) => $value !== null);
    }
}
