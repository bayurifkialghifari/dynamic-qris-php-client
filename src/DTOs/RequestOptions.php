<?php

declare(strict_types=1);

namespace Bayurifkialghifari\DynamicQris\DTOs;

use Bayurifkialghifari\DynamicQris\Support\Validation;
use InvalidArgumentException;

final readonly class RequestOptions
{
    public function __construct(
        public ?string $apiKey = null,
        public ?string $idempotencyKey = null,
        public ?string $shopeeToken = null,
        public ?int $timeout = null,
    ) {
        foreach (['apiKey', 'shopeeToken'] as $field) {
            if ($this->$field !== null) {
                Validation::text($this->$field, $field);
                if (preg_match('/[\r\n]/', $this->$field)) {
                    throw new InvalidArgumentException("{$field} must not contain newlines");
                }
            }
        }
        if ($idempotencyKey !== null && ! preg_match('/^[\x21-\x7e]{1,128}$/D', $idempotencyKey)) {
            throw new InvalidArgumentException('idempotencyKey must be 1–128 printable ASCII characters without spaces');
        }
        if ($timeout !== null) {
            Validation::integer($timeout, 'timeout');
        }
    }
}
