<?php

declare(strict_types=1);

namespace Bayurifkialghifari\DynamicQris\DTOs;

final readonly class ProviderAccount extends ResponseData
{
    public int $id;

    public string $provider;

    public string $label;

    public string $providerSelector;

    public bool $active;

    public bool $configured;

    public bool $hasQrisStatic;

    public string $status;

    public string $merchantName;

    public string $merchantCity;

    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->id = (int) ($data['id'] ?? 0);
        $this->provider = (string) ($data['provider'] ?? '');
        $this->label = (string) ($data['label'] ?? '');
        $this->providerSelector = (string) ($data['provider_selector'] ?? '');
        $this->active = (bool) ($data['active'] ?? false);
        $this->configured = (bool) ($data['configured'] ?? false);
        $this->hasQrisStatic = (bool) ($data['has_qris_static'] ?? false);
        $this->status = (string) ($data['status'] ?? '');
        $this->merchantName = (string) ($data['merchant_name'] ?? '');
        $this->merchantCity = (string) ($data['merchant_city'] ?? '');
    }

    public static function fromArray(array $data): self
    {
        return new self($data);
    }
}
