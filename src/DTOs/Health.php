<?php

declare(strict_types=1);

namespace Bayurifkialghifari\DynamicQris\DTOs;

final readonly class Health extends ResponseData
{
    public bool $success;

    public string $name;

    public string $version;

    public string $message;

    public string $timestamp;

    public int $uptimeSeconds;

    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->success = (bool) ($data['success'] ?? false);
        $this->name = (string) ($data['name'] ?? '');
        $this->version = (string) ($data['version'] ?? '');
        $this->message = (string) ($data['message'] ?? '');
        $this->timestamp = (string) ($data['timestamp'] ?? '');
        $this->uptimeSeconds = (int) ($data['uptime_seconds'] ?? 0);
    }

    public static function fromArray(array $data): self
    {
        return new self($data);
    }
}
