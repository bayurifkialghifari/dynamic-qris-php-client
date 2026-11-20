<?php

declare(strict_types=1);

namespace Bayurifkialghifari\DynamicQris\Modules;

use Bayurifkialghifari\DynamicQris\DTOs\WebhookEvent;
use Bayurifkialghifari\DynamicQris\DTOs\WebhookPayload;
use Bayurifkialghifari\DynamicQris\Exceptions\DynamicQrisException;
use JsonException;

final class WebhookModule
{
    /**
     * Generate HMAC-SHA256 signature formatted as 'sha256=<hex_digest>'.
     */
    public function sign(string $payload, string $secret): string
    {
        return 'sha256='.hash_hmac('sha256', $payload, $secret);
    }

    /**
     * Verify incoming signature against raw request body using constant-time comparison.
     */
    public function verifySignature(string $payload, string $signature, string $secret): bool
    {
        if ($signature === '' || $secret === '') {
            return false;
        }

        $clean = trim($signature);
        if (str_starts_with($clean, 'sha256=')) {
            $clean = substr($clean, 7);
        }

        if (! preg_match('/^[0-9a-fA-F]{64}$/D', $clean)) {
            return false;
        }

        $expected = hash_hmac('sha256', $payload, $secret);

        return hash_equals(strtolower($expected), strtolower($clean));
    }

    /**
     * Check idempotency delivery key to prevent double order fulfillment.
     *
     * @param  array<string>|callable(string): bool  $seenDeliveries
     */
    public function verifyDelivery(string $deliveryId, array|callable &$seenDeliveries): bool
    {
        if ($deliveryId === '') {
            return false;
        }

        if (is_array($seenDeliveries)) {
            if (in_array($deliveryId, $seenDeliveries, true)) {
                return false;
            }
            $seenDeliveries[] = $deliveryId;

            return true;
        }

        $alreadyProcessed = (bool) $seenDeliveries($deliveryId);

        return ! $alreadyProcessed;
    }

    /**
     * Construct and validate a typed WebhookEvent from raw payload and signature.
     *
     * @throws DynamicQrisException If signature verification fails or payload is malformed
     */
    public function constructEvent(
        string $payload,
        string $signature,
        string $secret,
        ?string $deliveryId = null,
    ): WebhookEvent {
        if (! $this->verifySignature($payload, $signature, $secret)) {
            throw new DynamicQrisException('Webhook signature verification failed', 401);
        }

        try {
            $data = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new DynamicQrisException('Malformed JSON webhook payload', 400, null, $e);
        }

        if (! is_array($data)) {
            throw new DynamicQrisException('Webhook payload must decode to an associative array', 400);
        }

        $event = (string) ($data['event'] ?? 'payment.success');
        $webhookPayload = WebhookPayload::fromArray($data);

        return new WebhookEvent(
            event: $event,
            payload: $webhookPayload,
            signature: $signature,
            rawBody: $payload,
            deliveryId: $deliveryId,
        );
    }
}
