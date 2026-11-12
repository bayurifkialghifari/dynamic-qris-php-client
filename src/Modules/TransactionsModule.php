<?php

declare(strict_types=1);

namespace Bayurifkialghifari\DynamicQris\Modules;

use Bayurifkialghifari\DynamicQris\DTOs\RequestOptions;
use Bayurifkialghifari\DynamicQris\DTOs\TransactionsQuery;
use Bayurifkialghifari\DynamicQris\DTOs\TransactionsResult;
use Bayurifkialghifari\DynamicQris\DynamicQrisClient;
use Bayurifkialghifari\DynamicQris\Support\Validation;

final class TransactionsModule
{
    public function __construct(private readonly DynamicQrisClient $client) {}

    public function list(TransactionsQuery|array $query = [], ?RequestOptions $options = null): TransactionsResult
    {
        $query = is_array($query) ? TransactionsQuery::fromArray($query) : $query;

        return TransactionsResult::fromArray($this->client->request('GET', '/transactions', $query->toArray(), $options, 'envelope'));
    }

    /** Current-month history in WIB, as defined by the gateway. */
    public function all(?string $provider = null, ?RequestOptions $options = null): TransactionsResult
    {
        Validation::provider($provider);

        return TransactionsResult::fromArray($this->client->request(
            'GET', '/transactions/all', array_filter(['provider' => $provider], fn ($value) => $value !== null), $options, 'envelope',
        ));
    }
}
