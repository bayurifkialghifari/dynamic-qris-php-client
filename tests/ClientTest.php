<?php

declare(strict_types=1);

namespace Bayurifkialghifari\DynamicQris\Tests;

use Bayurifkialghifari\DynamicQris\DTOs\CheckPaymentRequest;
use Bayurifkialghifari\DynamicQris\DTOs\CreateQrisRequest;
use Bayurifkialghifari\DynamicQris\DTOs\RequestOptions;
use Bayurifkialghifari\DynamicQris\DTOs\TransactionsQuery;
use Bayurifkialghifari\DynamicQris\DTOs\UpdateTokenRequest;
use Bayurifkialghifari\DynamicQris\DynamicQrisClient;
use Bayurifkialghifari\DynamicQris\Exceptions\DynamicQrisException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;

final class ClientTest extends TestCase
{
    public function test_account_is_typed_and_authenticated(): void
    {
        $this->fake('account');
        $account = $this->client()->account()->get();
        $this->assertSame(10000, $account->balance);
        $this->assertSame('shopee:store', $account->providers[0]->providerSelector);
        $this->assertSame($this->fixture['account']['data'], $account->toArray());
        $this->assertSame($account->toArray(), json_decode(json_encode($account), true));
        Http::assertSent(fn (Request $request) => $request->url() === 'https://gateway.example/proxy/v1/account'
            && $request->hasHeader('x-api-key', 'test-key') && $request->method() === 'GET');
    }

    #[DataProvider('creationStatuses')]
    public function test_creation_accepts_dto_arrays_and_replay(int $status, bool $useArray): void
    {
        $body = $this->fixture['qris'];
        $body['data']['idempotent_replay'] = $status === 200;
        Http::fake(fn () => Http::response($body, $status));
        $input = $useArray ? ['amount' => 10000, 'provider' => 'shopee:store'] : new CreateQrisRequest(10000, 'shopee:store');
        $qris = $this->client()->qris()->create($input, new RequestOptions(idempotencyKey: 'order-123'));
        $this->assertSame(10001, $qris->amount);
        $this->assertSame($body['data']['id'], $qris->id);
        $this->assertSame(10000, $qris->requestedAmount);
        $this->assertSame($status === 200, $qris->idempotentReplay);
        Http::assertSent(fn (Request $request) => $request->hasHeader('Idempotency-Key', 'order-123')
            && $request['amount'] === 10000 && $request['provider'] === 'shopee:store'
            && $request->url() === 'https://gateway.example/proxy/v1/qris/create');
    }

    public static function creationStatuses(): array
    {
        return [[201, false], [200, false], [201, true], [200, true]];
    }

    #[DataProvider('paymentStates')]
    public function test_payment_preserves_paid_pending_and_expired(string $state): void
    {
        $this->fake($state);
        $payment = $this->client()->payment()->check(
            new CheckPaymentRequest('qr_example', 'shopee:store'), new RequestOptions(shopeeToken: 'provider-token'),
        );
        $this->assertSame($this->fixture[$state], $payment->toArray());
        $this->assertSame($state === 'paid', $payment->paid);
        $this->assertSame(10001, $payment->data->amount);
        if ($payment->paid) {
            $this->assertSame('TX-001', $payment->transaction->transactionId);
            $this->assertSame('TX-001', $payment->data->transaction->transactionId);
        } else {
            $this->assertNull($payment->transaction);
            $this->assertNotNull($payment->data->expiresAt);
        }
        Http::assertSent(fn (Request $request) => $request->hasHeader('x-shopee-token', 'provider-token'));
    }

    public static function paymentStates(): array
    {
        return [['paid'], ['pending'], ['expired']];
    }

    public function test_transactions_preserve_totals_and_encode_cursor(): void
    {
        $this->fake('transactions');
        $result = $this->client()->transactions()->list(new TransactionsQuery(
            provider: 'gopay:store', startTime: 100, endTime: 200, pageSize: 50, nextPosition: 'cursor/next+1',
        ));
        $this->assertSame('10001', $result->totalAmount);
        $this->assertSame('TX-001', $result->transactions[0]->transactionId);
        $this->assertSame('cursor/next+1', $result->nextPosition);
        Http::assertSent(function (Request $request) {
            parse_str(parse_url($request->url(), PHP_URL_QUERY), $query);

            return $query === ['provider' => 'gopay:store', 'startTime' => '100', 'endTime' => '200', 'page_size' => '50', 'next_position' => 'cursor/next+1'];
        });
    }

    public function test_current_month_history_preserves_period(): void
    {
        $this->fake('allTransactions');
        $result = $this->client()->transactions()->all('gopay:store');
        $this->assertSame('2026-11-01 s/d 2026-11-12', $result->period);
        $this->assertSame(1, $result->totalCount);
        $this->assertNull($result->nextPosition);
    }

    #[DataProvider('tokenStates')]
    public function test_invalid_or_missing_token_is_a_status(string $state): void
    {
        $body = $this->fixture['tokenStatus'];
        $body['success'] = $state === 'valid';
        $body['data']['token_status'] = $state;
        Http::fake(fn () => Http::response($body));
        $result = $this->client()->token()->status('shopee:store');
        $this->assertSame($state, $result->tokenStatus);
        $this->assertSame($state === 'valid', $result->success);
        $this->assertSame('shopee:store', $result->providerSelector);
    }

    public static function tokenStates(): array
    {
        return [['invalid'], ['not_set'], ['valid']];
    }

    public function test_token_update_accepts_a_dto(): void
    {
        $this->fake('tokenUpdate');
        $result = $this->client()->token()->update(new UpdateTokenRequest('new-provider-token', 'shopee:store'));
        $this->assertSame('Token updated', $result->message);
        Http::assertSent(fn (Request $request) => $request['token'] === 'new-provider-token'
            && $request['provider'] === 'shopee:store' && $request->url() === 'https://gateway.example/proxy/update-token');
    }

    public function test_public_probes_and_qr_downloads_need_no_api_key(): void
    {
        Http::fake([
            '*/api/health' => Http::response($this->fixture['health']),
            '*/api/ready' => Http::response($this->fixture['ready']),
            '*/qr/qr_example?format=raw' => Http::response('000201'),
            '*/qr/1' => Http::response("\x89PNG\r\n\x1a\n", 200, ['Content-Type' => 'image/png']),
        ]);
        $client = new DynamicQrisClient('https://gateway.example');
        $this->assertSame(42, $client->system()->health()->uptimeSeconds);
        $this->assertSame('ready', $client->system()->ready()->database);
        $this->assertSame('000201', $client->qris()->raw('qr_example'));
        $this->assertSame("\x89PNG\r\n\x1a\n", $client->qris()->image(1));
        $this->assertSame('https://gateway.example/qr/qr%2Fone', $client->qris()->url('qr/one'));
        Http::assertNotSent(fn (Request $request) => $request->hasHeader('x-api-key'));
    }

    public function test_logs_are_authenticated(): void
    {
        $this->fake('logs');
        $this->assertSame([], $this->client()->system()->logs());
        Http::assertSent(fn (Request $request) => $request->hasHeader('x-api-key', 'test-key'));
    }

    public function test_tenant_override_does_not_mutate_the_singleton(): void
    {
        $this->fake('account');
        $this->client()->account()->get(new RequestOptions(apiKey: 'tenant-two'));
        $this->client()->account()->get();
        $recorded = Http::recorded();
        $this->assertSame(['tenant-two'], $recorded[0][0]->header('x-api-key'));
        $this->assertSame(['test-key'], $recorded[1][0]->header('x-api-key'));
    }

    #[DataProvider('httpStatuses')]
    public function test_api_errors_preserve_http_status_and_body(int $status): void
    {
        $body = ['success' => false, 'error' => 'Gateway rejected request'];
        Http::fake(fn () => Http::response($body, $status));
        try {
            $this->client()->qris()->create(['amount' => 10000]);
            $this->fail('Expected an API exception');
        } catch (DynamicQrisException $error) {
            $this->assertSame($status, $error->status);
            $this->assertSame($body, $error->response);
            $this->assertSame($body['error'], $error->getMessage());
        }
        Http::assertSentCount(1);
    }

    public static function httpStatuses(): array
    {
        return [[200], [400], [401], [402], [409], [429], [500], [503]];
    }

    public function test_qr_text_errors_are_preserved(): void
    {
        Http::fake(fn () => Http::response('QR expired', 410));
        try {
            $this->client()->qris()->image(1);
            $this->fail('Expected an exception');
        } catch (DynamicQrisException $error) {
            $this->assertSame(410, $error->status);
            $this->assertSame('QR expired', $error->response);
        }
    }

    #[DataProvider('malformedResponses')]
    public function test_malformed_json_envelopes_are_rejected(mixed $body): void
    {
        Http::fake(fn () => Http::response($body));
        $this->expectException(DynamicQrisException::class);
        $this->client()->account()->get();
    }

    public static function malformedResponses(): array
    {
        return [['not json'], [[]], [['success' => 'true']], [['success' => true]]];
    }

    public function test_malformed_token_status_is_rejected(): void
    {
        Http::fake(fn () => Http::response(['success' => false, 'error' => 'Unexpected error']));
        $this->expectException(DynamicQrisException::class);
        $this->client()->token()->status();
    }

    public function test_connection_failure_preserves_cause(): void
    {
        $cause = new ConnectionException('Network unavailable');
        Http::fake(fn () => throw $cause);
        try {
            $this->client()->account()->get();
            $this->fail('Expected an exception');
        } catch (DynamicQrisException $error) {
            $this->assertSame(0, $error->status);
            $this->assertSame($cause, $error->getPrevious());
        }
    }

    public function test_long_poll_extends_timeout_and_disables_redirects(): void
    {
        Http::fake(function ($request, $options) {
            $this->assertSame(138, $options['timeout']);
            $this->assertSame(10, $options['connect_timeout']);
            $this->assertFalse($options['allow_redirects']);

            return Http::response($this->fixture['pending']);
        });
        $this->client()->payment()->check(['token' => 'qr_example', 'wait' => true]);
    }

    public function test_explicit_timeout_takes_precedence_over_long_poll_default(): void
    {
        Http::fake(function ($request, $options) {
            $this->assertSame(5, $options['timeout']);

            return Http::response($this->fixture['pending']);
        });
        $this->client()->payment()->check(['token' => 'qr_example', 'wait' => true], new RequestOptions(timeout: 5));
    }
}
