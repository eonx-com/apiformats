<?php
declare(strict_types=1);

namespace EoneoPay\ApiFormats\Exceptions;

use EoneoPay\ApiFormats\Interfaces\ApiFormatsExceptionInterface;
use EoneoPay\Utils\Exceptions\RuntimeException;

abstract class ApiFormatterException extends RuntimeException implements ApiFormatsExceptionInterface
{
    /**
     * {@inheritdoc}
     */
    public function getErrorCode(): int
    {
        return self::DEFAULT_ERROR_CODE_RUNTIME;
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorSubCode(): int
    {
        return self::DEFAULT_ERROR_SUB_CODE;
    }
}
