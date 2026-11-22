<?php

declare(strict_types=1);

namespace App\Services;

use Bayurifkialghifari\DynamicQris\DTOs\CreatedTransaction;
use Bayurifkialghifari\DynamicQris\DTOs\CreateTransactionRequest;
use Bayurifkialghifari\DynamicQris\DTOs\RequestOptions;
use Bayurifkialghifari\DynamicQris\DynamicQrisClient;

final class CheckoutService
{
    public function __construct(private readonly DynamicQrisClient $client) {}

    public function create(string $orderId, int $amount): CreatedTransaction
    {
        // Persist the immutable order ID first, then store the returned token.
        return $this->client->transaction()->create(new CreateTransactionRequest(amount: $amount), new RequestOptions(idempotencyKey: $orderId));
    }
}
