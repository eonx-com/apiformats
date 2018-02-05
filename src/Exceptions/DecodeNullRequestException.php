<?php
declare(strict_types=1);

namespace EoneoPay\ApiFormats\Exceptions;

use EoneoPay\ApiFormats\Interfaces\ApiFormatsExceptionInterface;
use EoneoPay\Utils\Exceptions\RuntimeException;

class DecodeNullRequestException extends RuntimeException implements ApiFormatsExceptionInterface
{
    /**
     * Get Error code.
     *
     * @return int
     */
    public function getErrorCode(): int
    {
        return self::DEFAULT_ERROR_CODE_RUNTIME;
    }

    /**
     * Get Error sub-code.
     *
     * @return int
     */
    public function getErrorSubCode(): int
    {
        return self::DEFAULT_ERROR_SUB_CODE;
    }
}
