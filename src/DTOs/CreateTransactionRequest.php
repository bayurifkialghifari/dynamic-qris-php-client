<?php

declare(strict_types=1);

namespace Bayurifkialghifari\DynamicQris\DTOs;

use Bayurifkialghifari\DynamicQris\Support\Validation;

final readonly class CreateTransactionRequest
{
    public function __construct(
        public int $amount,
        public ?string $provider = null,
        public ?string $description = null,
        public ?array $products = null,
        public ?string $callbackUrl = null,
    ) {
        Validation::integer($amount, 'amount');
        Validation::provider($provider);
        Validation::url($callbackUrl, 'callbackUrl');
    }

    public static function fromArray(array $data): self
    {
        $amount = $data['amount'] ?? null;
        Validation::integer($amount, 'amount');

        return new self(
            amount: $amount,
            provider: $data['provider'] ?? null,
            description: $data['description'] ?? null,
            products: $data['products'] ?? null,
            callbackUrl: $data['callback_url'] ?? $data['callbackUrl'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'amount' => $this->amount,
            'provider' => $this->provider,
            'description' => $this->description,
            'products' => $this->products,
            'callback_url' => $this->callbackUrl,
        ], fn ($value) => $value !== null);
    }
}
