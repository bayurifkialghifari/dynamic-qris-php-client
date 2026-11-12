<?php

declare(strict_types=1);

namespace Bayurifkialghifari\DynamicQris\DTOs;

final readonly class PaymentData extends ResponseData
{
    public string $token;

    public int $amount;

    public ?string $expiresAt;

    public ?Transaction $transaction;

    public string $provider;

    public string $label;

    public string $providerSelector;

    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->token = (string) ($data['token'] ?? '');
        $this->amount = (int) ($data['amount'] ?? 0);
        $this->expiresAt = isset($data['expires_at']) ? (string) $data['expires_at'] : null;
        $this->transaction = isset($data['transaction']) ? Transaction::fromArray($data['transaction']) : null;
        $this->provider = (string) ($data['provider'] ?? '');
        $this->label = (string) ($data['label'] ?? '');
        $this->providerSelector = (string) ($data['provider_selector'] ?? '');
    }

    public static function fromArray(array $data): self
    {
        return new self($data);
    }
}
