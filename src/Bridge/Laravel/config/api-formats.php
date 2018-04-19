<?php
declare(strict_types=1);

use EoneoPay\ApiFormats\RequestEncoders\JsonApiRequestEncoder;
use EoneoPay\ApiFormats\RequestEncoders\JsonRequestEncoder;
use EoneoPay\ApiFormats\RequestEncoders\UrlEncodedDataRequestEncoder;
use EoneoPay\ApiFormats\RequestEncoders\XmlRequestEncoder;

return [
    'formats' => [
        JsonRequestEncoder::class => ['application/json'],
        JsonApiRequestEncoder::class => ['application/vnd.api\+json'],
        UrlEncodedDataRequestEncoder::class => ['application/x-www-form-urlencoded'],
        XmlRequestEncoder::class => ['(application|text)/xml']
    ]
];
