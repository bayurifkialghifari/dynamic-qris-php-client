<?php

declare(strict_types=1);

namespace Bayurifkialghifari\DynamicQris\DTOs;

final readonly class Account extends ResponseData
{
    public string $userId;

    public string $username;

    public string $firstName;

    public string $apiKey;

    public bool $hasShopeeToken;

    public bool $hasQrisStatic;

    public bool $tokenValid;

    public string $activeProvider;

    public ?string $activeProviderLabel;

    public ?string $activeProviderSelector;

    /** @var list<ProviderAccount> */
    public array $providers;

    public bool $isActive;

    public int $balance;

    public int $qrisCreationFee;

    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->userId = (string) ($data['user_id'] ?? '');
        $this->username = (string) ($data['username'] ?? '');
        $this->firstName = (string) ($data['first_name'] ?? '');
        $this->apiKey = (string) ($data['api_key'] ?? '');
        $this->hasShopeeToken = (bool) ($data['has_shopee_token'] ?? false);
        $this->hasQrisStatic = (bool) ($data['has_qris_static'] ?? false);
        $this->tokenValid = (bool) ($data['token_valid'] ?? false);
        $this->activeProvider = (string) ($data['active_provider'] ?? '');
        $this->activeProviderLabel = isset($data['active_provider_label']) ? (string) $data['active_provider_label'] : null;
        $this->activeProviderSelector = isset($data['active_provider_selector']) ? (string) $data['active_provider_selector'] : null;
        $this->providers = array_map(fn (array $item) => ProviderAccount::fromArray($item), $data['providers'] ?? []);
        $this->isActive = (bool) ($data['is_active'] ?? false);
        $this->balance = (int) ($data['balance'] ?? 0);
        $this->qrisCreationFee = (int) ($data['qris_creation_fee'] ?? 0);
    }

    public static function fromArray(array $data): self
    {
        return new self($data);
    }
}
