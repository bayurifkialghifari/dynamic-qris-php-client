<?php

declare(strict_types=1);

namespace Bayurifkialghifari\DynamicQris\DTOs;

use Bayurifkialghifari\DynamicQris\Support\Validation;

final readonly class CreateQrisRequest
{
    public function __construct(
        public int $amount,
        public ?string $provider = null,
        public ?string $callbackUrl = null,
        public ?string $customOrderId = null,
        public ?array $products = null,
        public ?string $customerName = null,
        public ?string $customerEmail = null,
        public ?string $customerPhone = null,
        public ?string $redirectUrl = null,
        public ?string $cancelUrl = null,
        public ?int $expiresInMinutes = null,
    ) {
        Validation::integer($amount, 'amount');
        Validation::provider($provider);
        Validation::url($callbackUrl, 'callbackUrl');
        Validation::url($redirectUrl, 'redirectUrl');
        Validation::url($cancelUrl, 'cancelUrl');
        if ($expiresInMinutes !== null) {
            Validation::integer($expiresInMinutes, 'expiresInMinutes');
        }
    }

    public static function fromArray(array $data): self
    {
        $amount = $data['amount'] ?? null;
        Validation::integer($amount, 'amount');

        return new self(
            amount: $amount,
            provider: $data['provider'] ?? null,
            callbackUrl: $data['callback_url'] ?? $data['callbackUrl'] ?? null,
            customOrderId: $data['custom_order_id'] ?? $data['customOrderId'] ?? null,
            products: $data['products'] ?? $data['items'] ?? null,
            customerName: $data['customer_name'] ?? $data['customerName'] ?? null,
            customerEmail: $data['customer_email'] ?? $data['customerEmail'] ?? null,
            customerPhone: $data['customer_phone'] ?? $data['customerPhone'] ?? null,
            redirectUrl: $data['redirect_url'] ?? $data['redirectUrl'] ?? null,
            cancelUrl: $data['cancel_url'] ?? $data['cancelUrl'] ?? null,
            expiresInMinutes: isset($data['expires_in_minutes']) ? (int) $data['expires_in_minutes'] : (isset($data['expiresInMinutes']) ? (int) $data['expiresInMinutes'] : null),
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'amount' => $this->amount,
            'provider' => $this->provider,
            'callback_url' => $this->callbackUrl,
            'custom_order_id' => $this->customOrderId,
            'products' => $this->products,
            'customer_name' => $this->customerName,
            'customer_email' => $this->customerEmail,
            'customer_phone' => $this->customerPhone,
            'redirect_url' => $this->redirectUrl,
            'cancel_url' => $this->cancelUrl,
            'expires_in_minutes' => $this->expiresInMinutes,
        ], fn ($value) => $value !== null);
    }
}
