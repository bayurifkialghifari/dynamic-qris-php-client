<?php

declare(strict_types=1);

namespace Bayurifkialghifari\DynamicQris\DTOs;

final readonly class TokenStatus extends ResponseData
{
    public bool $success;

    public string $tokenStatus;

    public string $message;

    public string $provider;

    public string $label;

    public string $providerSelector;

    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->success = (bool) ($data['success'] ?? false);
        $this->tokenStatus = (string) $data['data']['token_status'];
        $this->message = (string) $data['data']['message'];
        $this->provider = (string) ($data['data']['provider'] ?? '');
        $this->label = (string) ($data['data']['label'] ?? '');
        $this->providerSelector = (string) ($data['data']['provider_selector'] ?? '');
    }

    public static function fromArray(array $data): self
    {
        return new self($data);
    }
}
