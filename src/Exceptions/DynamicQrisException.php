<?php

declare(strict_types=1);

namespace Bayurifkialghifari\DynamicQris\Exceptions;

use RuntimeException;
use Throwable;

class DynamicQrisException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $status = 0,
        public readonly mixed $response = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $status, $previous);
    }
}
