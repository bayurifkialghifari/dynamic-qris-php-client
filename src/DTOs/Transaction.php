<?php

declare(strict_types=1);

namespace Bayurifkialghifari\DynamicQris\DTOs;

final readonly class Transaction extends ResponseData
{
    public string $transactionId;

    public int $amount;

    public string $status;

    public string $time;

    public string $issuer;

    public ?int $timestamp;

    public string $provider;

    public string $label;

    public string $providerSelector;

    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->transactionId = (string) ($data['transactionId'] ?? '');
        $this->amount = (int) ($data['amount'] ?? 0);
        $this->status = (string) ($data['status'] ?? '');
        $this->time = (string) ($data['time'] ?? '');
        $this->issuer = (string) ($data['issuer'] ?? '');
        $this->timestamp = isset($data['timestamp']) ? (int) $data['timestamp'] : null;
        $this->provider = (string) ($data['provider'] ?? '');
        $this->label = (string) ($data['label'] ?? '');
        $this->providerSelector = (string) ($data['provider_selector'] ?? '');
    }

    public static function fromArray(array $data): self
    {
        return new self($data);
    }
}
