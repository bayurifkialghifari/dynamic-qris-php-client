<?php

declare(strict_types=1);

namespace Bayurifkialghifari\DynamicQris\DTOs;

final readonly class TokenUpdate extends ResponseData
{
    public string $message;

    public string $provider;

    public string $label;

    public string $providerSelector;

    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->message = (string) ($data['message'] ?? '');
        $this->provider = (string) ($data['provider'] ?? '');
        $this->label = (string) ($data['label'] ?? '');
        $this->providerSelector = (string) ($data['provider_selector'] ?? '');
    }

    public static function fromArray(array $data): self
    {
        return new self($data);
    }
}
