<?php

declare(strict_types=1);

namespace Bayurifkialghifari\DynamicQris\Tests;

use Bayurifkialghifari\DynamicQris\DTOs\WebhookEvent;
use Bayurifkialghifari\DynamicQris\DTOs\WebhookPayload;
use Bayurifkialghifari\DynamicQris\Exceptions\DynamicQrisException;
use Bayurifkialghifari\DynamicQris\Facades\DynamicQris;
use Bayurifkialghifari\DynamicQris\Modules\WebhookModule;

final class WebhookTest extends TestCase
{
    private WebhookModule $webhook;

    private string $secret = 'whsec_test_secret_1234567890abcdef';

    private string $payload;

    protected function setUp(): void
    {
        parent::setUp();
        $this->webhook = new WebhookModule;
        $this->payload = json_encode([
            'event' => 'payment.success',
            'token' => 'tok_test_123',
            'status' => 'success',
            'amount' => 10000,
            'unique_code' => 1,
            'total_amount' => 10001,
            'description' => 'Test payment',
            'products' => [
                ['name' => 'Item A', 'price' => 10000, 'quantity' => 1],
            ],
            'issuer' => 'ShopeePay',
            'transaction_id' => 'TX-12345',
            'paid_at' => '2026-11-20T05:00:00.000Z',
            'created_at' => '2026-11-20T04:45:00.000Z',
        ], JSON_THROW_ON_ERROR);
    }

    public function test_it_generates_and_verifies_sha256_signature(): void
    {
        $signature = $this->webhook->sign($this->payload, $this->secret);
        $this->assertStringStartsWith('sha256=', $signature);
        $this->assertTrue($this->webhook->verifySignature($this->payload, $signature, $this->secret));

        // Accepts signature without sha256= prefix as well
        $plainHex = substr($signature, 7);
        $this->assertTrue($this->webhook->verifySignature($this->payload, $plainHex, $this->secret));
    }

    public function test_it_rejects_tampered_payload_or_wrong_secret(): void
    {
        $signature = $this->webhook->sign($this->payload, $this->secret);

        $tampered = str_replace('10001', '99999', $this->payload);
        $this->assertFalse($this->webhook->verifySignature($tampered, $signature, $this->secret));
        $this->assertFalse($this->webhook->verifySignature($this->payload, $signature, 'wrong_secret'));
        $this->assertFalse($this->webhook->verifySignature($this->payload, 'invalid_sig', $this->secret));
        $this->assertFalse($this->webhook->verifySignature($this->payload, '', $this->secret));
    }

    public function test_it_constructs_typed_webhook_event(): void
    {
        $signature = $this->webhook->sign($this->payload, $this->secret);
        $deliveryId = 'del_uuid_12345';

        $event = $this->webhook->constructEvent(
            payload: $this->payload,
            signature: $signature,
            secret: $this->secret,
            deliveryId: $deliveryId,
        );

        $this->assertInstanceOf(WebhookEvent::class, $event);
        $this->assertSame('payment.success', $event->event);
        $this->assertSame($deliveryId, $event->deliveryId);
        $this->assertSame($signature, $event->signature);

        $payload = $event->payload;
        $this->assertInstanceOf(WebhookPayload::class, $payload);
        $this->assertSame('tok_test_123', $payload->token);
        $this->assertSame(10000, $payload->amount);
        $this->assertSame(1, $payload->uniqueCode);
        $this->assertSame(10001, $payload->totalAmount);
        $this->assertSame('TX-12345', $payload->transactionId);
        $this->assertSame('ShopeePay', $payload->issuer);
        $this->assertCount(1, $payload->products);
    }

    public function test_it_throws_exception_on_invalid_signature(): void
    {
        $this->expectException(DynamicQrisException::class);
        $this->expectExceptionMessage('Webhook signature verification failed');

        $this->webhook->constructEvent($this->payload, 'sha256=bad_signature', $this->secret);
    }

    public function test_it_verifies_idempotency_delivery_key(): void
    {
        $seen = [];
        $this->assertTrue($this->webhook->verifyDelivery('del_1', $seen));
        $this->assertFalse($this->webhook->verifyDelivery('del_1', $seen)); // Duplicate
        $this->assertTrue($this->webhook->verifyDelivery('del_2', $seen));

        $dbMock = ['del_existing' => true];
        $isProcessed = fn (string $id) => isset($dbMock[$id]);

        $this->assertFalse($this->webhook->verifyDelivery('del_existing', $isProcessed));
        $this->assertTrue($this->webhook->verifyDelivery('del_fresh', $isProcessed));
    }

    public function test_it_is_accessible_via_client_and_facade(): void
    {
        $client = $this->client();
        $this->assertInstanceOf(WebhookModule::class, $client->webhook);
        $this->assertInstanceOf(WebhookModule::class, $client->webhook());
        $this->assertInstanceOf(WebhookModule::class, DynamicQris::webhook());
    }
}
