<?php

declare(strict_types=1);

namespace Bayurifkialghifari\DynamicQris\DTOs;

use Bayurifkialghifari\DynamicQris\Support\Validation;
use InvalidArgumentException;

final readonly class TransactionsQuery
{
    public function __construct(
        public ?string $provider = null,
        public ?int $startTime = null,
        public ?int $endTime = null,
        public ?int $pageSize = null,
        public ?string $nextPosition = null,
    ) {
        Validation::provider($provider);
        foreach (['startTime', 'endTime'] as $field) {
            if ($this->$field !== null) {
                Validation::integer($this->$field, $field);
            }
        }
        if ($pageSize !== null) {
            Validation::integer($pageSize, 'page_size', 100);
        }
        if ($startTime !== null && $endTime !== null && $startTime > $endTime) {
            throw new InvalidArgumentException('startTime must not exceed endTime');
        }
    }

    public static function fromArray(array $data): self
    {
        Validation::provider($data['provider'] ?? null);
        foreach (['startTime', 'endTime', 'page_size'] as $field) {
            if (isset($data[$field])) {
                Validation::integer($data[$field], $field);
            }
        }

        return new self(
            provider: $data['provider'] ?? null, startTime: $data['startTime'] ?? null,
            endTime: $data['endTime'] ?? null, pageSize: $data['page_size'] ?? null,
            nextPosition: $data['next_position'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'provider' => $this->provider, 'startTime' => $this->startTime, 'endTime' => $this->endTime,
            'page_size' => $this->pageSize, 'next_position' => $this->nextPosition,
        ], fn ($value) => $value !== null);
    }
}
