<?php
declare(strict_types=1);

namespace Tests\EoneoPay\ApiFormats\Bridge\Laravel\Exceptions;

use EoneoPay\ApiFormats\Exceptions\UnsupportedRequestFormatException;
use EoneoPay\Utils\Interfaces\BaseExceptionInterface;
use Tests\EoneoPay\ApiFormats\TestCases\BridgeLaravelExceptionsTestCase;

class UnsupportedRequestFormatExceptionTest extends BridgeLaravelExceptionsTestCase
{
    /**
     * Exception should return valid codes.
     *
     * @return void
     */
    public function testGettersFromBaseExceptionInterface(): void
    {
        $this->processExceptionCodesTest(
            new UnsupportedRequestFormatException(),
            BaseExceptionInterface::DEFAULT_ERROR_CODE_RUNTIME,
            BaseExceptionInterface::DEFAULT_ERROR_SUB_CODE,
            BaseExceptionInterface::DEFAULT_STATUS_CODE_RUNTIME
        );
    }
}
