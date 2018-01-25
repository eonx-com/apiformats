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
     * @param string|null $accept
     *
     * @return \Zend\Diactoros\ServerRequest
     */
    protected function getRequest(string $accept = null): ServerRequest
    {
        return new ServerRequest([], [], null, null, 'php://input', ['accept' => $accept ?? 'application/json']);
    }
}
