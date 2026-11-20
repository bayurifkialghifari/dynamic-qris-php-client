<?php

declare(strict_types=1);

namespace Bayurifkialghifari\DynamicQris\DTOs;

final readonly class WebhookPayload extends ResponseData
{
    public string $event;

    public string $token;

    public string $status;

    public int $amount;

    public int $uniqueCode;

    public int $totalAmount;

    public ?string $description;

    public ?array $products;

    public ?string $issuer;

    public ?string $transactionId;

    public string $paidAt;

    public string $createdAt;

    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->event = (string) ($data['event'] ?? 'payment.success');
        $this->token = (string) ($data['token'] ?? '');
        $this->status = (string) ($data['status'] ?? 'success');
        $this->amount = (int) ($data['amount'] ?? 0);
        $this->uniqueCode = (int) ($data['unique_code'] ?? 0);
        $this->totalAmount = (int) ($data['total_amount'] ?? 0);
        $this->description = isset($data['description']) ? (string) $data['description'] : null;
        $this->products = isset($data['products']) && is_array($data['products']) ? $data['products'] : null;
        $this->issuer = isset($data['issuer']) ? (string) $data['issuer'] : null;
        $this->transactionId = isset($data['transaction_id']) ? (string) $data['transaction_id'] : null;
        $this->paidAt = (string) ($data['paid_at'] ?? '');
        $this->createdAt = (string) ($data['created_at'] ?? '');
    }

    public static function fromArray(array $data): self
    {
        return new self($data);
    }
}
