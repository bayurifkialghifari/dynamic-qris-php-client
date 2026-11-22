<?php

declare(strict_types=1);

namespace Bayurifkialghifari\DynamicQris\DTOs;

final readonly class CreatedTransaction extends ResponseData
{
    public string $id;

    public string $token;

    public string $paymentType;

    public ?string $paymentLink;

    public ?string $callbackUrl;

    public ?string $description;

    public ?array $products;

    public ?string $qrisUrl;

    public ?string $qrisString;

    public ?string $bankName;

    public ?string $bankLogo;

    public ?string $accountNumber;

    public ?string $accountHolderName;

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
        $this->paymentType = (string) ($data['payment_type'] ?? 'qris');
        $this->paymentLink = isset($data['payment_link']) ? (string) $data['payment_link'] : (isset($data['paymentLink']) ? (string) $data['paymentLink'] : null);
        $this->callbackUrl = isset($data['callback_url']) ? (string) $data['callback_url'] : (isset($data['callbackUrl']) ? (string) $data['callbackUrl'] : null);
        $this->description = isset($data['description']) ? (string) $data['description'] : null;
        $this->products = isset($data['products']) && is_array($data['products']) ? $data['products'] : null;
        $this->qrisUrl = isset($data['qris_url']) && $data['qris_url'] !== null ? (string) $data['qris_url'] : (isset($data['qrisUrl']) && $data['qrisUrl'] !== null ? (string) $data['qrisUrl'] : null);
        $this->qrisString = isset($data['qris_string']) && $data['qris_string'] !== null ? (string) $data['qris_string'] : (isset($data['qrisString']) && $data['qrisString'] !== null ? (string) $data['qrisString'] : null);
        $this->bankName = isset($data['bank_name']) && $data['bank_name'] !== null ? (string) $data['bank_name'] : (isset($data['bankName']) && $data['bankName'] !== null ? (string) $data['bankName'] : null);
        $this->bankLogo = isset($data['bank_logo']) && $data['bank_logo'] !== null ? (string) $data['bank_logo'] : (isset($data['bankLogo']) && $data['bankLogo'] !== null ? (string) $data['bankLogo'] : null);
        $this->accountNumber = isset($data['account_number']) && $data['account_number'] !== null ? (string) $data['account_number'] : (isset($data['accountNumber']) && $data['accountNumber'] !== null ? (string) $data['accountNumber'] : null);
        $this->accountHolderName = isset($data['account_holder_name']) && $data['account_holder_name'] !== null ? (string) $data['account_holder_name'] : (isset($data['accountHolderName']) && $data['accountHolderName'] !== null ? (string) $data['accountHolderName'] : null);
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
