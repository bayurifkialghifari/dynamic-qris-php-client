<?php

declare(strict_types=1);

namespace Bayurifkialghifari\DynamicQris\Support;

use InvalidArgumentException;

final class Validation
{
    public static function text(mixed $value, string $name): void
    {
        if (! is_string($value) || trim($value) === '') {
            throw new InvalidArgumentException("{$name} must be a non-empty string");
        }
    }

    public static function integer(mixed $value, string $name, int $max = 9007199254740991): void
    {
        if (! is_int($value) || $value <= 0 || $value > $max) {
            throw new InvalidArgumentException("{$name} must be a positive integer up to {$max}");
        }
    }

    public static function provider(mixed $value, bool $shopeeOnly = false): void
    {
        if ($value === null) {
            return;
        }
        if (! is_string($value) || ! preg_match('/^(shopee|gopay)(:[a-z0-9_-]{1,64})?$/D', $value)) {
            throw new InvalidArgumentException('provider must be shopee, gopay, or kind:label');
        }
        if ($shopeeOnly && ! str_starts_with($value, 'shopee')) {
            throw new InvalidArgumentException('Token updates only support ShopeePay');
        }
    }
}
