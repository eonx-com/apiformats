<?php
declare(strict_types=1);

use EoneoPay\ApiFormats\Encoders\JsonApiEncoder;
use EoneoPay\ApiFormats\Encoders\JsonEncoder;
use EoneoPay\ApiFormats\Encoders\UrlEncodedDataEncoder;
use EoneoPay\ApiFormats\Encoders\XmlEncoder;

return [
    'formats' => [
        JsonEncoder::class => ['application/json'],
        JsonApiEncoder::class => ['application/vnd.api\+json'],
        UrlEncodedDataEncoder::class => ['application/x-www-form-urlencoded'],
        XmlEncoder::class => ['(application|text)/xml']
    ]
];
