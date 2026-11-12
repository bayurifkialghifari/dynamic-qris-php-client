<?php

declare(strict_types=1);

namespace Bayurifkialghifari\DynamicQris;

use Bayurifkialghifari\DynamicQris\DTOs\RequestOptions;
use Bayurifkialghifari\DynamicQris\Exceptions\DynamicQrisException;
use Bayurifkialghifari\DynamicQris\Modules\AccountModule;
use Bayurifkialghifari\DynamicQris\Modules\PaymentModule;
use Bayurifkialghifari\DynamicQris\Modules\QrisModule;
use Bayurifkialghifari\DynamicQris\Modules\SystemModule;
use Bayurifkialghifari\DynamicQris\Modules\TokenModule;
use Bayurifkialghifari\DynamicQris\Modules\TransactionsModule;
use Bayurifkialghifari\DynamicQris\Support\Validation;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use JsonException;

final class DynamicQrisClient
{
    public readonly AccountModule $account;

    public readonly QrisModule $qris;

    public readonly PaymentModule $payment;

    public readonly TransactionsModule $transactions;

    public readonly TokenModule $token;

    public readonly SystemModule $system;

    private readonly string $baseUrl;

    public function __construct(
        string $baseUrl,
        private readonly ?string $apiKey = null,
        public readonly int $timeout = 30,
        private readonly int $connectTimeout = 10,
    ) {
        $url = parse_url($baseUrl);
        if (! $url || ! isset($url['host']) || ! in_array($url['scheme'] ?? '', ['http', 'https'], true)
            || isset($url['user']) || isset($url['pass']) || isset($url['query']) || isset($url['fragment'])) {
            throw new InvalidArgumentException('baseUrl must be an HTTP(S) URL without credentials, query, or fragment');
        }
        Validation::integer($timeout, 'timeout');
        Validation::integer($connectTimeout, 'connectTimeout');
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->account = new AccountModule($this);
        $this->qris = new QrisModule($this);
        $this->payment = new PaymentModule($this);
        $this->transactions = new TransactionsModule($this);
        $this->token = new TokenModule($this);
        $this->system = new SystemModule($this);
    }

    public function account(): AccountModule
    {
        return $this->account;
    }

    public function qris(): QrisModule
    {
        return $this->qris;
    }

    public function payment(): PaymentModule
    {
        return $this->payment;
    }

    public function transactions(): TransactionsModule
    {
        return $this->transactions;
    }

    public function token(): TokenModule
    {
        return $this->token;
    }

    public function system(): SystemModule
    {
        return $this->system;
    }

    public function url(string $path): string
    {
        return $this->baseUrl.'/'.ltrim($path, '/');
    }

    /** @internal Modules use this transport. Returns data, envelope, or raw bytes. */
    public function request(
        string $method, string $path, array $data = [], ?RequestOptions $options = null,
        string $mode = 'data', bool $authenticated = true, ?int $minimumTimeout = null,
    ): array|string {
        $options ??= new RequestOptions;
        $headers = ['Accept' => match ($mode) {
            'binary' => 'image/png', 'text' => 'text/plain', default => 'application/json',
        }];
        if ($authenticated) {
            $key = $options->apiKey ?? $this->apiKey;
            Validation::text($key, 'apiKey');
            if (preg_match('/[\r\n]/', $key)) {
                throw new InvalidArgumentException('apiKey must not contain newlines');
            }
            $headers['x-api-key'] = $key;
        }
        if ($options->idempotencyKey !== null) {
            $headers['Idempotency-Key'] = $options->idempotencyKey;
        }
        if ($options->shopeeToken !== null) {
            $headers['x-shopee-token'] = $options->shopeeToken;
        }
        try {
            $http = Http::withHeaders($headers)->asJson()
                ->timeout($options->timeout ?? max($this->timeout, $minimumTimeout ?? 0))
                ->connectTimeout($this->connectTimeout)->withoutRedirecting();
            $response = match ($method) {
                'GET' => $http->get($this->url($path), $data),
                'POST' => $http->post($this->url($path), $data),
                default => throw new InvalidArgumentException('Unsupported HTTP method'),
            };
        } catch (ConnectionException $error) {
            throw new DynamicQrisException('Unable to reach the QRIS gateway or request timed out', previous: $error);
        }
        $raw = $response->body();
        if ($response->successful() && in_array($mode, ['text', 'binary'], true)) {
            return $raw;
        }
        try {
            $body = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            $body = $raw;
        }
        if (! $response->successful()) {
            $message = is_array($body) && is_string($body['error'] ?? null)
                ? $body['error'] : 'Gateway returned HTTP '.$response->status();
            throw new DynamicQrisException($message, $response->status(), $body);
        }
        if (! is_array($body) || ! is_bool($body['success'] ?? null)) {
            throw new DynamicQrisException('Invalid gateway response envelope', $response->status(), $body);
        }
        if (! $body['success'] && $mode !== 'status') {
            throw new DynamicQrisException(is_string($body['error'] ?? null) ? $body['error'] : 'Gateway request failed', $response->status(), $body);
        }
        if ($mode === 'data') {
            if (! is_array($body['data'] ?? null)) {
                throw new DynamicQrisException('Gateway response is missing data', $response->status(), $body);
            }

            return $body['data'];
        }
        if ($mode === 'status' && ! in_array($body['data']['token_status'] ?? null, ['valid', 'invalid', 'not_set'], true)) {
            throw new DynamicQrisException('Invalid token status response', $response->status(), $body);
        }

        return $body;
    }
}
