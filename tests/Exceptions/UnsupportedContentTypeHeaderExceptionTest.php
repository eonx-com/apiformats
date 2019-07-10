<?php
declare(strict_types=1);

namespace Tests\EoneoPay\ApiFormats\Exceptions;

use EoneoPay\ApiFormats\Exceptions\UnsupportedContentTypeHeaderException;
use EoneoPay\Utils\Interfaces\BaseExceptionInterface;
use Tests\EoneoPay\ApiFormats\TestCases\BridgeLaravelExceptionsTestCase;

class UnsupportedContentTypeHeaderExceptionTest extends BridgeLaravelExceptionsTestCase
{
    /**
     * Exception should return valid codes.
     *
     * @return void
     */
    public function testGettersFromBaseExceptionInterface(): void
    {
        $this->processExceptionCodesTest(
            new UnsupportedContentTypeHeaderException(),
            BaseExceptionInterface::DEFAULT_ERROR_CODE_RUNTIME,
            BaseExceptionInterface::DEFAULT_ERROR_SUB_CODE,
            406
        );
    }
}
