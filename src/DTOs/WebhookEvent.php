<?php

declare(strict_types=1);

namespace Bayurifkialghifari\DynamicQris\DTOs;

final readonly class WebhookEvent
{
    public function __construct(
        public string $event,
        public WebhookPayload $payload,
        public string $signature,
        public string $rawBody,
        public ?string $deliveryId = null,
    ) {}
}
