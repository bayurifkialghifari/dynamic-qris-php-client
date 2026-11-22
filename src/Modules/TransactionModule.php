<?php

declare(strict_types=1);

namespace Bayurifkialghifari\DynamicQris\Modules;

use Bayurifkialghifari\DynamicQris\DTOs\CreatedTransaction;
use Bayurifkialghifari\DynamicQris\DTOs\CreateQrisRequest;
use Bayurifkialghifari\DynamicQris\DTOs\CreateTransactionRequest;
use Bayurifkialghifari\DynamicQris\DTOs\RequestOptions;
use Bayurifkialghifari\DynamicQris\DynamicQrisClient;

final class TransactionModule
{
    public function __construct(private readonly DynamicQrisClient $client) {}

    public function create(CreateTransactionRequest|CreateQrisRequest|array $request, ?RequestOptions $options = null): CreatedTransaction
    {
        if (is_array($request)) {
            $request = CreateTransactionRequest::fromArray($request);
        }

        return CreatedTransaction::fromArray(
            $this->client->request('POST', '/v1/transaction', $request->toArray(), $options)
        );
    }
}
