# Changelog

## 0.3.0

- Add `POST /v1/transaction` support via `TransactionModule` (`$client->transaction->create()`, `$client->transactions->create()`, and `DynamicQris::transaction()->create()`).
- Add `CreateTransactionRequest` and `CreatedTransaction` DTOs with support for QRIS and Bank Transfer payments (including bank account fields: `bankName`, `bankLogo`, `accountNumber`, `accountHolderName`, and `paymentType`).
- Support `bank_transfer` provider type (e.g. `bank_transfer:bca-utama`) in provider validation.
- Deprecate `POST /v1/qris/create` (`QrisModule::create()`) as legacy with runtime deprecation notice and `@deprecated` annotation while maintaining full backward compatibility.
- Update documentation and examples to use `transaction()->create()`.

## 0.2.1

- Align `CreateQrisRequest` and `Qris` DTO strictly with gateway OpenAPI specification.
- Tag and release version bump for Composer and Packagist synchronization.

## 0.2.0

- Add `WebhookModule`, `WebhookPayload`, and `WebhookEvent` for verifying HMAC-SHA256 signatures (`x-karuhun-signature`), parsing typed events (`constructEvent`), and checking idempotency keys (`verifyDelivery`).
- Add `webhook()` accessor to `DynamicQrisClient` and `DynamicQris` facade.
- Support `custom` provider accounts (e.g. `custom:dana-1`, `custom:bca-bisnis`) alongside ShopeePay and GoPay.
- Support `POST /v1/qris/create` fields: `amount`, `provider`, `description`, `products` (items with `name`, `price`, `qty`), and `callback_url`.
- Add `payment_link`, `callback_url`, `description`, and `products` properties to `Qris` DTO.

## 0.1.1

- Use the Karuhun QRIS service URL as the default Laravel configuration and in examples.
- Add website and Telegram registration links, Packagist installation instructions, and version/download badges.
- Keep SDK tests self-contained with HTTP fakes.

## 0.1.0

- Initial modular SDK for account, QRIS, payments, transactions, provider credentials, and gateway probes.
- Per-request authentication overrides, idempotency headers, raw/PNG QR downloads, and long-poll timeout handling.
- Typed requests/responses, mocked contract tests, examples, documentation, and CI.
