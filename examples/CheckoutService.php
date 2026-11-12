<?php

declare(strict_types=1);

namespace App\Services;

use Bayurifkialghifari\DynamicQris\DTOs\CreateQrisRequest;
use Bayurifkialghifari\DynamicQris\DTOs\Qris;
use Bayurifkialghifari\DynamicQris\DTOs\RequestOptions;
use Bayurifkialghifari\DynamicQris\DynamicQrisClient;

final class CheckoutService
{
    public function __construct(private readonly DynamicQrisClient $client) {}

    public function create(string $orderId, int $amount): Qris
    {
        // Persist the immutable order ID first, then store the returned token.
        return $this->client->qris()->create(new CreateQrisRequest(amount: $amount), new RequestOptions(idempotencyKey: $orderId));
    }
}
