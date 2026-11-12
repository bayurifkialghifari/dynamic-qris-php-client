# Dynamic QRIS PHP Client for Laravel

Client Laravel modular untuk Dynamic QRIS Gateway dengan DTO request/response, facade, service provider, dan konfigurasi publish. Mendukung ShopeePay, GoPay, pemilihan akun provider, idempotency, mutasi, serta long-poll pembayaran.

## Persyaratan dan instalasi

PHP 8.3+ dan Laravel 12 atau 13. Paket belum dipublish ke Packagist.

Di composer.json aplikasi Laravel, tambahkan repository GitHub berikut:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/bayurifkialghifari/dynamic-qris-php-client"
    }
  ]
}
```

```bash
composer require bayurifkialghifari/dynamic-qris-php-client:^0.1
php artisan vendor:publish --tag=dynamic-qris-config
```

Service provider dan facade terdaftar lewat Laravel package discovery.

Untuk development lokal, repository Composer dapat memakai type=path, url=../dynamic-qris-php-client, serta options.versions dengan version 0.1.0.

## Konfigurasi

```env
DYNAMIC_QRIS_BASE_URL=http://localhost:4000
DYNAMIC_QRIS_API_KEY=your-merchant-api-key
DYNAMIC_QRIS_TIMEOUT=30
DYNAMIC_QRIS_CONNECT_TIMEOUT=10
```

Config berada di config/dynamic-qris.php. Timeout PHP memakai detik. base_url mendukung prefix reverse proxy.

## Quick start dengan DTO

```php
use Bayurifkialghifari\DynamicQris\DTOs\CreateQrisRequest;
use Bayurifkialghifari\DynamicQris\DTOs\CheckPaymentRequest;
use Bayurifkialghifari\DynamicQris\DTOs\RequestOptions;
use Bayurifkialghifari\DynamicQris\Facades\DynamicQris;

$account = DynamicQris::account()->get();
echo $account->balance;

$qr = DynamicQris::qris()->create(
    new CreateQrisRequest(amount: 10000, provider: 'shopee:store'),
    new RequestOptions(idempotencyKey: 'order-123'),
);
echo $qr->qrisUrl;
echo $qr->amount; // Nominal yang harus dibayar, termasuk kode unik.

$result = DynamicQris::payment()->check(new CheckPaymentRequest(token: $qr->token));
if ($result->paid) {
    echo $result->transaction->transactionId;
}
```

Array request juga didukung. Field array mengikuti kontrak API:

```php
$qr = DynamicQris::qris()->create(
    ['amount' => 10000, 'provider' => 'gopay:store'],
    new RequestOptions(idempotencyKey: 'order-456'),
);
```

## Dependency injection

```php
use Bayurifkialghifari\DynamicQris\DynamicQrisClient;

final class CheckoutService
{
    public function __construct(private DynamicQrisClient $qris) {}

    public function balance(): int
    {
        return $this->qris->account()->get()->balance;
    }
}
```

Modul dapat diakses lewat method ($client->qris()) atau properti ($client->qris). Untuk beberapa gateway, buat new DynamicQrisClient(baseUrl: ..., apiKey: ...) di dalam aplikasi Laravel. Override per tenant memakai RequestOptions(apiKey: 'tenant-key') dan tidak mengubah singleton.

## Modul dan response

| Method | DTO / hasil |
| --- | --- |
| account()->get(options?) | Account dengan ProviderAccount[] |
| qris()->create(request, options?) | Qris |
| qris()->url(id) | URL gambar, tanpa HTTP request |
| qris()->raw(id, options?) | String payload QRIS |
| qris()->image(id, options?) | String bytes PNG |
| payment()->check(request, options?) | PaymentResult, PaymentData, Transaction |
| transactions()->list(query?, options?) | TransactionsResult dengan Transaction[] |
| transactions()->all(provider?, options?) | TransactionsResult dengan period/totalCount |
| token()->status(provider?, options?) | TokenStatus |
| token()->update(request, options?) | TokenUpdate |
| system()->health(options?) | Health, public |
| system()->ready(options?) | Readiness, public |
| system()->logs(options?) | Array log, perlu API key |

Properti DTO menggunakan camelCase (qrisUrl, requestedAmount, providerSelector, totalAmount). Semua response DTO menyimpan raw serta menyediakan toArray() / JSON serialization untuk mengembalikan kontrak API asli. Metadata paid/status dan total_amount tetap tersimpan pada response yang relevan.

## Long polling

```php
$result = DynamicQris::payment()->check(new CheckPaymentRequest(
    token: 'saved-qris-token',
    wait: true,
    timeout: 120,
    pollingInterval: 3,
));
// $result->status: success, pending, atau expired.
```

wait memakai satu request server long-poll. timeout adalah detik (1–300) dan pollingInterval detik (2–300). Timeout HTTP otomatis menjadi sekurangnya timeout + pollingInterval + 15 detik. RequestOptions(timeout: 5) tetap menimpa default tersebut. Jalankan long polling di worker jika request web aplikasi memiliki batas waktu pendek.

## Mutasi, QR PNG, dan token

```php
use Bayurifkialghifari\DynamicQris\DTOs\TransactionsQuery;
use Bayurifkialghifari\DynamicQris\DTOs\UpdateTokenRequest;

$page = DynamicQris::transactions()->list(new TransactionsQuery(
    provider: 'gopay:store', startTime: time() - 86400, endTime: time(),
    pageSize: 50, nextPosition: '',
));
echo $page->totalAmount; // String dari gateway.
// Lanjutkan dengan $page->nextPosition, hingga cursor kosong.

$month = DynamicQris::transactions()->all('gopay:store');
echo $month->period; // Bulan berjalan WIB.
file_put_contents(storage_path('app/payment.png'), DynamicQris::qris()->image('saved-qris-token'));

$status = DynamicQris::token()->status('shopee:store');
echo $status->tokenStatus; // valid, invalid, atau not_set.
DynamicQris::token()->update(new UpdateTokenRequest(token: 'new-token', provider: 'shopee:store'));
```

Status kredensial invalid/not_set dengan HTTP 200 dikembalikan sebagai DTO, meskipun success: false. Update token hanya untuk ShopeePay; sesi GoPay dikelola lewat bot gateway. Header custom ShopeePay tersedia melalui RequestOptions(shopeeToken: '...') pada pengecekan pembayaran.

## Idempotency, nominal, dan error

Gunakan integer Rupiah positif. requestedAmount adalah nominal pesanan dan amount mencakup uniqueCode. creationFee dipotong dari saldo merchant; biaya aktual dapat dibaca lewat account()->get()->qrisCreationFee. Provider kosong memakai akun aktif, sedangkan token pembayaran terikat akun pembuat QRIS.

Simpan token dan idempotency key bersama pesanan. Request ulang dengan key serta input yang sama mengembalikan QRIS lama (idempotentReplay: true) tanpa membuat pembayaran baru. Client mengirim satu request per pemanggilan.

```php
use Bayurifkialghifari\DynamicQris\Exceptions\DynamicQrisException;

try {
    DynamicQris::qris()->create(['amount' => 10000], new RequestOptions(idempotencyKey: 'order-789'));
} catch (DynamicQrisException $error) {
    if ($error->status === 402) {
        // Tawarkan isi saldo merchant melalui bot gateway.
    }
    report($error);
}
```

Exception menyimpan status HTTP dan response asli. Gangguan koneksi/timeout menggunakan status 0 dan getPrevious(). Input tidak valid menghasilkan InvalidArgumentException sebelum request. Redirect ditolak. RequestOptions juga menerima apiKey untuk override autentikasi per request.

## Testing dan formatting

```bash
composer install
composer test
composer format
composer format:check
composer validate --strict
```

PHPUnit + Orchestra Testbench memverifikasi HTTP contract, DTO, facade, singleton, config publish, dan validasi dengan Http::fake(). Semua request di luar fake ditolak. Contoh checkout ada di [examples/CheckoutService.php](examples/CheckoutService.php); [CHANGELOG](CHANGELOG.md), [MIT License](LICENSE).
