<?php

declare(strict_types=1);

namespace Bayurifkialghifari\DynamicQris\Modules;

use Bayurifkialghifari\DynamicQris\DTOs\Account;
use Bayurifkialghifari\DynamicQris\DTOs\RequestOptions;
use Bayurifkialghifari\DynamicQris\DynamicQrisClient;

final class AccountModule
{
    public function __construct(private readonly DynamicQrisClient $client) {}

    public function get(?RequestOptions $options = null): Account
    {
        return Account::fromArray($this->client->request('GET', '/v1/account', options: $options));
    }
}
