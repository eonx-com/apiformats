<?php
declare(strict_types=1);

namespace Tests\EoneoPay\ApiFormats\TestCases;

use PHPUnit\Framework\TestCase;
use Zend\Diactoros\ServerRequest;

class RequestEncoderGuesserTestCase extends TestCase
{
    /**
     * Get server request.
     *
     * @return \Zend\Diactoros\ServerRequest
     */
    protected function getRequest(): ServerRequest
    {
        return new ServerRequest([], [], null, null, 'php://input', ['accept' => 'application/json']);
    }
}
