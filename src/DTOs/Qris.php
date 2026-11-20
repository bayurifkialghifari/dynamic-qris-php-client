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

    public ?string $callbackUrl;

    public ?string $paymentUrl;

    public ?string $qrImageUrl;

    public ?string $merchantName;

    public ?int $totalAmount;

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
        $this->callbackUrl = isset($data['callback_url']) ? (string) $data['callback_url'] : (isset($data['callbackUrl']) ? (string) $data['callbackUrl'] : null);
        $this->paymentUrl = isset($data['payment_url']) ? (string) $data['payment_url'] : (isset($data['paymentUrl']) ? (string) $data['paymentUrl'] : null);
        $this->qrImageUrl = isset($data['qr_image_url']) ? (string) $data['qr_image_url'] : (isset($data['qrImageUrl']) ? (string) $data['qrImageUrl'] : null);
        $this->merchantName = isset($data['merchant_name']) ? (string) $data['merchant_name'] : (isset($data['merchantName']) ? (string) $data['merchantName'] : null);
        $this->totalAmount = isset($data['total_amount']) ? (int) $data['total_amount'] : (isset($data['totalAmount']) ? (int) $data['totalAmount'] : null);
    }

    public static function fromArray(array $data): self
    {
        return new self($data);
    }
}
