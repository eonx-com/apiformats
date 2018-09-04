<?php
declare(strict_types=1);

namespace Tests\EoneoPay\ApiFormats\Bridge\Laravel\Exceptions;

use EoneoPay\ApiFormats\Bridge\Laravel\Exceptions\InvalidPsr7FactoryException;
use EoneoPay\Utils\Interfaces\BaseExceptionInterface;
use Tests\EoneoPay\ApiFormats\TestCases\BridgeLaravelExceptionsTestCase;

class InvalidPsr7FactoryExceptionTest extends BridgeLaravelExceptionsTestCase
{
    /**
     * Exception should return valid codes.
     *
     * @return void
     */
    public function testGettersFromBaseExceptionInterface(): void
    {
        $this->processExceptionCodesTest(
            new InvalidPsr7FactoryException(),
            BaseExceptionInterface::DEFAULT_ERROR_CODE_RUNTIME,
            BaseExceptionInterface::DEFAULT_ERROR_SUB_CODE,
            BaseExceptionInterface::DEFAULT_STATUS_CODE_RUNTIME
        );
    }
}
