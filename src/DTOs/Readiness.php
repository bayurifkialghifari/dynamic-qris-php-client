<?php

declare(strict_types=1);

namespace Bayurifkialghifari\DynamicQris\DTOs;

final readonly class Readiness extends ResponseData
{
    public bool $success;

    public string $database;

    public string $timestamp;

    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->success = (bool) ($data['success'] ?? false);
        $this->database = (string) ($data['database'] ?? '');
        $this->timestamp = (string) ($data['timestamp'] ?? '');
    }

    public static function fromArray(array $data): self
    {
        return new self($data);
    }
}
