<?php

declare(strict_types=1);

namespace Bayurifkialghifari\DynamicQris\DTOs;

final readonly class TransactionsResult extends ResponseData
{
    public bool $success;

    public string $totalAmount;

    public string $provider;

    public string $label;

    public string $providerSelector;

    /** @var list<Transaction> */
    public array $transactions;

    public ?string $nextPosition;

    public ?int $total;

    public ?int $totalCount;

    public ?string $period;

    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->success = (bool) ($data['success'] ?? false);
        $this->totalAmount = (string) ($data['total_amount'] ?? '');
        $this->provider = (string) ($data['provider'] ?? '');
        $this->label = (string) ($data['label'] ?? '');
        $this->providerSelector = (string) ($data['provider_selector'] ?? '');
        $this->transactions = array_map(fn (array $item) => Transaction::fromArray($item), $data['data']['transactions'] ?? []);
        $this->nextPosition = isset($data['data']['next_position']) ? (string) $data['data']['next_position'] : null;
        $this->total = isset($data['data']['total']) ? (int) $data['data']['total'] : null;
        $this->totalCount = isset($data['data']['total_count']) ? (int) $data['data']['total_count'] : null;
        $this->period = isset($data['data']['period']) ? (string) $data['data']['period'] : null;
    }

    public static function fromArray(array $data): self
    {
        return new self($data);
    }
}
