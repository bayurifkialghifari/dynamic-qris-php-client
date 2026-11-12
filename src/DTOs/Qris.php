<?php

declare(strict_types=1);

namespace Bayurifkialghifari\DynamicQris\DTOs;

final readonly class Qris extends ResponseData
{
    public string $id;

    public string $token;

    public string $qrisUrl;

    public string $qrisString;

    public int $amount;

    public int $requestedAmount;

    public int $uniqueCode;

    public int $creationFee;

    public int $balanceRemaining;

    public string $expiresAt;

    public int $expiresInSeconds;

    public bool $idempotentReplay;

    public string $provider;

    public string $label;

    public string $providerSelector;

    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->id = (string) ($data['id'] ?? '');
        $this->token = (string) ($data['token'] ?? '');
        $this->qrisUrl = (string) ($data['qris_url'] ?? '');
        $this->qrisString = (string) ($data['qris_string'] ?? '');
        $this->amount = (int) ($data['amount'] ?? 0);
        $this->requestedAmount = (int) ($data['requested_amount'] ?? 0);
        $this->uniqueCode = (int) ($data['unique_code'] ?? 0);
        $this->creationFee = (int) ($data['creation_fee'] ?? 0);
        $this->balanceRemaining = (int) ($data['balance_remaining'] ?? 0);
        $this->expiresAt = (string) ($data['expires_at'] ?? '');
        $this->expiresInSeconds = (int) ($data['expires_in_seconds'] ?? 0);
        $this->idempotentReplay = (bool) ($data['idempotent_replay'] ?? false);
        $this->provider = (string) ($data['provider'] ?? '');
        $this->label = (string) ($data['label'] ?? '');
        $this->providerSelector = (string) ($data['provider_selector'] ?? '');
    }

    public static function fromArray(array $data): self
    {
        return new self($data);
    }
}
