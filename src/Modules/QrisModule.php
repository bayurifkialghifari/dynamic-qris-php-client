<?php

declare(strict_types=1);

namespace Bayurifkialghifari\DynamicQris\Modules;

use Bayurifkialghifari\DynamicQris\DTOs\CreateQrisRequest;
use Bayurifkialghifari\DynamicQris\DTOs\Qris;
use Bayurifkialghifari\DynamicQris\DTOs\RequestOptions;
use Bayurifkialghifari\DynamicQris\DynamicQrisClient;
use Bayurifkialghifari\DynamicQris\Support\Validation;

final class QrisModule
{
    public function __construct(private readonly DynamicQrisClient $client) {}

    public function create(CreateQrisRequest|array $request, ?RequestOptions $options = null): Qris
    {
        $request = is_array($request) ? CreateQrisRequest::fromArray($request) : $request;

        return Qris::fromArray($this->client->request('POST', '/v1/qris/create', $request->toArray(), $options));
    }

    public function url(string|int $id): string
    {
        if (is_int($id)) {
            Validation::integer($id, 'id');
        } else {
            Validation::text($id, 'id');
        }

        return $this->client->url('/qr/'.rawurlencode((string) $id));
    }

    public function raw(string|int $id, ?RequestOptions $options = null): string
    {
        $this->url($id);

        return $this->client->request('GET', '/qr/'.rawurlencode((string) $id), ['format' => 'raw'], $options, 'text', false);
    }

    public function image(string|int $id, ?RequestOptions $options = null): string
    {
        $this->url($id);

        return $this->client->request('GET', '/qr/'.rawurlencode((string) $id), [], $options, 'binary', false);
    }
}
