<?php
declare(strict_types=1);

use EoneoPay\ApiFormats\RequestEncoders\JsonApiRequestEncoder;
use EoneoPay\ApiFormats\RequestEncoders\JsonRequestEncoder;
use EoneoPay\ApiFormats\RequestEncoders\XmlRequestEncoder;

return [
    'formats' => [
        JsonRequestEncoder::class => ['application/json'],
        XmlRequestEncoder::class => ['(application|xml)/xml'],
        JsonApiRequestEncoder::class => ['application/vnd.api\+json']
    ]
];
