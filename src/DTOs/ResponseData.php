<?php

declare(strict_types=1);

namespace Bayurifkialghifari\DynamicQris\DTOs;

use JsonSerializable;

abstract readonly class ResponseData implements JsonSerializable
{
    public function __construct(public array $raw) {}

    public function toArray(): array
    {
        return $this->raw;
    }

    public function jsonSerialize(): array
    {
        return $this->raw;
    }
}
