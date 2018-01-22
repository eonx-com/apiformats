<?php
declare(strict_types=1);

namespace Tests\EoneoPay\ApiFormats\EventListeners;

use EoneoPay\ApiFormats\EventListeners\SetFormatOnRequestListener;
use EoneoPay\ApiFormats\Exceptions\UnsupportedRequestFormatException;
use Symfony\Component\HttpFoundation\Request;
use Tests\EoneoPay\ApiFormats\TestCases\EventListenersTestCase;

class SetFormatOnRequestListenerTest extends EventListenersTestCase
{
    /**
     * @var array
     */
    private static $formats = [
        'json' => ['application/json'],
        'xml' => ['application/xml', 'text/xml']
    ];

    /**
     * Listener should fallback to json format if no format guessed.
     */
    public function testFallbackToDefaultFormat(): void
    {
        $request = new Request();
        $event = $this->getGetResponseEvent($request);

        (new SetFormatOnRequestListener(static::$formats))->onKernelRequest($event);

        self::assertEquals('json', $request->getRequestFormat());
    }

    /**
     * Listener should throw exception if requested format not supported.
     */
    public function testUnsupportedRequestFormat(): void
    {
        $this->expectException(UnsupportedRequestFormatException::class);
        
        $request = new Request();
        $request->headers->set('accept', 'invalid');

        $event = $this->getGetResponseEvent($request);

        (new SetFormatOnRequestListener(static::$formats))->onKernelRequest($event);
    }

    /**
     * Listener should set XML format based on accept header.
     */
    public function testGuessFormatFromAcceptHeader(): void
    {
        $request = new Request();
        $request->headers->set('accept', 'application/xml');

        $event = $this->getGetResponseEvent($request);

        (new SetFormatOnRequestListener(static::$formats))->onKernelRequest($event);

        self::assertEquals('xml', $request->getRequestFormat());
    }

    /**
     * Listener should set XML format based on accept header.
     */
    public function testGuessFormatFromContentTypeHeader(): void
    {
        $request = new Request();
        $request->headers->set('content-type', 'text/xml');

        $event = $this->getGetResponseEvent($request);

        (new SetFormatOnRequestListener(static::$formats))->onKernelRequest($event);

        self::assertEquals('xml', $request->getRequestFormat());
    }

    /**
     * Listener should give priority to accept header even if content-type is set.
     */
    public function testPriorityToAcceptHeader(): void
    {
        $request = new Request();
        $request->headers->set('content-type', 'application/json');
        $request->headers->set('accept', 'application/xml');

        $event = $this->getGetResponseEvent($request);

        (new SetFormatOnRequestListener(static::$formats))->onKernelRequest($event);

        self::assertEquals('xml', $request->getRequestFormat());
    }
}
