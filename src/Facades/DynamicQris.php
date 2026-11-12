<?php

declare(strict_types=1);

namespace Bayurifkialghifari\DynamicQris\Facades;

use Bayurifkialghifari\DynamicQris\DynamicQrisClient;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Bayurifkialghifari\DynamicQris\Modules\AccountModule account()
 * @method static \Bayurifkialghifari\DynamicQris\Modules\QrisModule qris()
 * @method static \Bayurifkialghifari\DynamicQris\Modules\PaymentModule payment()
 * @method static \Bayurifkialghifari\DynamicQris\Modules\TransactionsModule transactions()
 * @method static \Bayurifkialghifari\DynamicQris\Modules\TokenModule token()
 * @method static \Bayurifkialghifari\DynamicQris\Modules\SystemModule system()
 *
 * @see DynamicQrisClient
 */
final class DynamicQris extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return DynamicQrisClient::class;
    }
}
