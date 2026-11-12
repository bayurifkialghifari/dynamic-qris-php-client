<?php

declare(strict_types=1);

namespace Bayurifkialghifari\DynamicQris\Modules;

use Bayurifkialghifari\DynamicQris\DTOs\CheckPaymentRequest;
use Bayurifkialghifari\DynamicQris\DTOs\PaymentResult;
use Bayurifkialghifari\DynamicQris\DTOs\RequestOptions;
use Bayurifkialghifari\DynamicQris\DynamicQrisClient;

final class PaymentModule
{
    public function __construct(private readonly DynamicQrisClient $client) {}

    public function check(CheckPaymentRequest|array $request, ?RequestOptions $options = null): PaymentResult
    {
        $request = is_array($request) ? CheckPaymentRequest::fromArray($request) : $request;
        $minimumTimeout = $request->wait ? $request->timeout + $request->pollingInterval + 15 : null;

        return PaymentResult::fromArray($this->client->request(
            'POST', '/v1/payment/check', $request->toArray(), $options, 'envelope', minimumTimeout: $minimumTimeout,
        ));
    }
}
