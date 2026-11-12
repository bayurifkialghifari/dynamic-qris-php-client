<?php

declare(strict_types=1);

namespace Bayurifkialghifari\DynamicQris\DTOs;

use Bayurifkialghifari\DynamicQris\Support\Validation;
use InvalidArgumentException;

final readonly class CheckPaymentRequest
{
    public function __construct(
        public string $token,
        public ?string $provider = null,
        public ?int $startTime = null,
        public bool $wait = false,
        public int $timeout = 120,
        public int $pollingInterval = 3,
    ) {
        Validation::text($token, 'token');
        Validation::provider($provider);
        if ($startTime !== null) {
            Validation::integer($startTime, 'startTime');
        }
        Validation::integer($timeout, 'timeout', 300);
        Validation::integer($pollingInterval, 'polling_interval', 300);
        if ($pollingInterval < 2) {
            throw new InvalidArgumentException('polling_interval must be at least 2 seconds');
        }
    }

    public static function fromArray(array $data): self
    {
        Validation::text($data['token'] ?? null, 'token');
        Validation::provider($data['provider'] ?? null);
        foreach (['startTime', 'timeout', 'polling_interval'] as $field) {
            if (isset($data[$field])) {
                Validation::integer($data[$field], $field);
            }
        }
        if (isset($data['wait']) && ! is_bool($data['wait'])) {
            throw new InvalidArgumentException('wait must be a boolean');
        }

        return new self(
            token: $data['token'], provider: $data['provider'] ?? null,
            startTime: $data['startTime'] ?? null, wait: $data['wait'] ?? false,
            timeout: $data['timeout'] ?? 120, pollingInterval: $data['polling_interval'] ?? 3,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'token' => $this->token, 'provider' => $this->provider, 'startTime' => $this->startTime,
            'wait' => $this->wait, 'timeout' => $this->timeout, 'polling_interval' => $this->pollingInterval,
        ], fn ($value) => $value !== null);
    }
}
