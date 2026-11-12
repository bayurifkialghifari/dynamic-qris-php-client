<?php

declare(strict_types=1);

namespace Bayurifkialghifari\DynamicQris\DTOs;

final readonly class PaymentResult extends ResponseData
{
    public bool $success;

    public bool $paid;

    public string $status;

    public string $provider;

    public string $label;

    public string $providerSelector;

    public PaymentData $data;

    public ?Transaction $transaction;

    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->success = (bool) ($data['success'] ?? false);
        $this->paid = (bool) ($data['paid'] ?? false);
        $this->status = (string) ($data['status'] ?? '');
        $this->provider = (string) ($data['provider'] ?? '');
        $this->label = (string) ($data['label'] ?? '');
        $this->providerSelector = (string) ($data['provider_selector'] ?? '');
        $this->data = PaymentData::fromArray($data['data']);
        $this->transaction = isset($data['transaction']) ? Transaction::fromArray($data['transaction']) : null;
    }

    public static function fromArray(array $data): self
    {
        return new self($data);
    }
}
