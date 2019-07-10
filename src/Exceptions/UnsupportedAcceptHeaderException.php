<?php
declare(strict_types=1);

namespace EoneoPay\ApiFormats\Exceptions;

final class UnsupportedAcceptHeaderException extends ApiFormatterException
{
    /**
     * @noinspection PhpMissingParentCallCommonInspection Parent call intentionally overloaded
     *
     * @inheritdoc
     */
    public function getStatusCode(): int
    {
        // Return 406 status code 'Not Acceptable' if we can't find an encoder for the Accept header
        return 406;
    }
}
