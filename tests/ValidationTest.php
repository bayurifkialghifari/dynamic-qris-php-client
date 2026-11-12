<?php

declare(strict_types=1);

namespace Bayurifkialghifari\DynamicQris\Tests;

use Bayurifkialghifari\DynamicQris\DTOs\CheckPaymentRequest;
use Bayurifkialghifari\DynamicQris\DTOs\CreateQrisRequest;
use Bayurifkialghifari\DynamicQris\DTOs\RequestOptions;
use Bayurifkialghifari\DynamicQris\DTOs\TransactionsQuery;
use Bayurifkialghifari\DynamicQris\DTOs\UpdateTokenRequest;
use Bayurifkialghifari\DynamicQris\DynamicQrisClient;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;

final class ValidationTest extends TestCase
{
    #[DataProvider('invalidAmounts')]
    public function test_array_amounts_reject_floats_strings_and_nonpositive_values(mixed $amount): void
    {
        Http::fake();
        try {
            $this->client()->qris()->create(['amount' => $amount]);
            $this->fail('Expected invalid input');
        } catch (InvalidArgumentException) {
            Http::assertNothingSent();
        }
    }

    public static function invalidAmounts(): array
    {
        return [[0], [-1], [1.5], ['1000'], [9007199254740992]];
    }

    #[DataProvider('invalidSelectors')]
    public function test_invalid_provider_selectors_are_rejected(string $provider): void
    {
        $this->expectException(InvalidArgumentException::class);
        new CreateQrisRequest(1000, $provider);
    }

    public static function invalidSelectors(): array
    {
        return [['other'], ['shopee:'], ['gopay:Invalid'], ['shopee:a:b']];
    }

    #[DataProvider('invalidKeys')]
    public function test_invalid_idempotency_keys_are_rejected(string $key): void
    {
        $this->expectException(InvalidArgumentException::class);
        new RequestOptions(idempotencyKey: $key);
    }

    public static function invalidKeys(): array
    {
        return [[''], [str_repeat('x', 129)], ["line\nbreak"]];
    }

    public function test_request_dto_keeps_false_and_empty_cursor(): void
    {
        $request = CheckPaymentRequest::fromArray(['token' => 'qr', 'wait' => false]);
        $this->assertFalse($request->toArray()['wait']);
        $this->assertArrayNotHasKey('provider', $request->toArray());
        $this->assertSame('', TransactionsQuery::fromArray(['next_position' => ''])->toArray()['next_position']);
    }

    public function test_string_boolean_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        CheckPaymentRequest::fromArray(['token' => 'qr', 'wait' => 'false']);
    }

    public function test_invalid_poll_budget_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new CheckPaymentRequest('qr', timeout: 301);
    }

    public function test_invalid_poll_interval_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new CheckPaymentRequest('qr', pollingInterval: 1);
    }

    public function test_reversed_transaction_range_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new TransactionsQuery(startTime: 200, endTime: 100);
    }

    public function test_gopay_token_updates_are_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new UpdateTokenRequest('token', 'gopay:store');
    }

    public function test_private_endpoints_require_an_api_key(): void
    {
        Http::fake();
        $this->expectException(InvalidArgumentException::class);
        (new DynamicQrisClient('https://gateway.example'))->account()->get();
    }

    #[DataProvider('invalidUrls')]
    public function test_invalid_base_urls_are_rejected(string $baseUrl): void
    {
        $this->expectException(InvalidArgumentException::class);
        new DynamicQrisClient($baseUrl);
    }

    public static function invalidUrls(): array
    {
        return [['ftp://gateway.example'], ['https://u:p@gateway.example'], ['https://gateway.example?q=1'], ['https://gateway.example#fragment']];
    }
}
