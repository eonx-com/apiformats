<?php
declare(strict_types=1);

namespace Tests\EoneoPay\ApiFormats\TestCases;

use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

abstract class BridgeLaravelMiddlewaresTestCase extends TestCase
{
    /**
     * Get request instance.
     *
     * @param string|null $content
     * @param string[]|null $headers
     *
     * @return \Illuminate\Http\Request
     */
    protected function getRequest(?string $content = null, ?array $headers = null): Request
    {
        $request = new Request([], [], [], [], [], [], $content);

        $headers = \array_merge($headers ?? [], ['X_ORIGINAL_URL' => 'https://eoneopay.com.au']);

        foreach ($headers ?? [] as $header => $value) {
            $request->headers->set($header, $value);
        }

        $request->setMethod(Request::METHOD_POST);

        return $request;
    }
}
