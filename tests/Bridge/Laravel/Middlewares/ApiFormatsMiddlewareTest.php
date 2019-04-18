<?php
declare(strict_types=1);

namespace Tests\EoneoPay\ApiFormats\Bridge\Laravel\Middlewares;

use EoneoPay\ApiFormats\Bridge\Laravel\Middlewares\ApiFormatsMiddleware;
use EoneoPay\ApiFormats\Bridge\Laravel\Responses\FormattedApiResponse;
use EoneoPay\ApiFormats\Bridge\Laravel\Responses\NoContentApiResponse;
use EoneoPay\ApiFormats\EncoderGuesser;
use EoneoPay\ApiFormats\Encoders\JsonEncoder;
use EoneoPay\ApiFormats\Encoders\XmlEncoder;
use EoneoPay\ApiFormats\Exceptions\UnsupportedRequestFormatException;
use EoneoPay\ApiFormats\External\Libraries\Psr7\Psr7Factory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\EoneoPay\ApiFormats\Stubs\SerializableInterfaceStub;
use Tests\EoneoPay\ApiFormats\TestCases\BridgeLaravelMiddlewaresTestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) High coupling required due to different formats
 */
class ApiFormatsMiddlewareTest extends BridgeLaravelMiddlewaresTestCase
{
    /**
     * Middleware should set default encoder on request if exception thrown wile guessing encoder.
     *
     * @return void
     *
     * @throws \EoneoPay\ApiFormats\Bridge\Laravel\Exceptions\InvalidPsr7FactoryException
     * @throws \EoneoPay\ApiFormats\Exceptions\ApiFormatterException
     */
    public function testDefaultEncoderOnRequestIfException(): void
    {
        $this->expectException(UnsupportedRequestFormatException::class);

        $psr7Factory = new Psr7Factory();
        $encoderGuesser = new EncoderGuesser([JsonEncoder::class => ['application/json']]);
        $request = $this->getRequest(null, ['accept' => 'invalid']);

        (new ApiFormatsMiddleware($encoderGuesser, $psr7Factory))->handle($request, function (): void {
        });

        self::assertInstanceOf(JsonEncoder::class, $request->attributes->get('_encoder'));
    }

    /**
     * Middleware should set the right encoder on the request, replace inputs on the request and return
     * an Laravel response.
     *
     * @return void
     *
     * @throws \EoneoPay\ApiFormats\Bridge\Laravel\Exceptions\InvalidPsr7FactoryException
     * @throws \EoneoPay\ApiFormats\Exceptions\ApiFormatterException
     */
    public function testHandleFormatsProperly(): void
    {
        $formats = [
            JsonEncoder::class => [
                'mime_type' => 'application/json',
                'content' => '{"email":"email@eoneopay.com.au"}'
            ],
            XmlEncoder::class => [
                'mime_type' => 'application/xml',
                'content' => '<data><email>email@eoneopay.com.au</email></data>'
            ]
        ];

        $middleware = new ApiFormatsMiddleware(new EncoderGuesser($formats), new Psr7Factory());
        $next = static function (Request $request) {
            return $request->all();
        };

        foreach ($formats as $encoder => $test) {
            $request = $this->getRequest($test['content'], ['accept' => $test['mime_type']]);
            $response = $middleware->handle($request, $next);

            self::assertInstanceOf($encoder, $request->attributes->get('_encoder'));
            self::assertInstanceOf(Response::class, $response);
            /** @var \Illuminate\Http\Response $response */
            self::assertSame($test['mime_type'], $response->headers->get('Content-Type'));

            switch ($encoder) {
                case JsonEncoder::class:
                    self::assertSame($test['content'], $response->getContent());
                    break;

                case XmlEncoder::class:
                    self::assertXmlStringEqualsXmlString($test['content'], $response->getContent());
                    break;
            }
        }
    }

    /**
     * Middleware should return empty response when NoContentApiResponse class is handled.
     *
     * @return void
     *
     * @throws \EoneoPay\ApiFormats\Bridge\Laravel\Exceptions\InvalidPsr7FactoryException
     * @throws \EoneoPay\ApiFormats\Exceptions\ApiFormatterException
     */
    public function testNoContentResponse(): void
    {
        $psr7Factory = new Psr7Factory();

        // Irrelevant - Will not be used
        $encoderGuesser = new EncoderGuesser([JsonEncoder::class => ['application/json']]);
        $request = $this->getRequest();
        $emptyResponse = new NoContentApiResponse();

        $next = static function (Request $request) use ($emptyResponse) {
            return $emptyResponse;
        };

        $response = (new ApiFormatsMiddleware($encoderGuesser, $psr7Factory))->handle($request, $next);
        self::assertEmpty($response->getContent());
        self::assertSame(204, $response->getStatusCode());
    }

    /**
     * Middleware should return directly response if closure result is a laravel response.
     *
     * @return void
     *
     * @throws \EoneoPay\ApiFormats\Bridge\Laravel\Exceptions\InvalidPsr7FactoryException
     * @throws \EoneoPay\ApiFormats\Exceptions\ApiFormatterException
     */
    public function testReturnDirectlyResponseIfAlreadyLaravelOne(): void
    {
        $psr7Factory = new Psr7Factory();
        $encoderGuesser = new EncoderGuesser([JsonEncoder::class => ['application/json']]);
        $request = $this->getRequest();
        $laravelResponse = new Response();

        $next = static function (Request $request) use ($laravelResponse) {
            return $laravelResponse;
        };

        $response = (new ApiFormatsMiddleware($encoderGuesser, $psr7Factory))->handle($request, $next);

        self::assertSame(\spl_object_hash($laravelResponse), \spl_object_hash($response));
    }

    /**
     * Middleware should return laravel response with right information if closure result is
     * formatted api response.
     *
     * @return void
     *
     * @throws \EoneoPay\ApiFormats\Bridge\Laravel\Exceptions\InvalidPsr7FactoryException
     * @throws \EoneoPay\ApiFormats\Exceptions\ApiFormatterException
     */
    public function testReturnRightFormattedResponseIfFormattedApiResponse(): void
    {
        $psr7Factory = new Psr7Factory();
        $encoderGuesser = new EncoderGuesser([JsonEncoder::class => ['application/json']]);
        $request = $this->getRequest();
        $formattedApiResponse = new FormattedApiResponse(
            ['email' => 'email@eoneopay.com.au'],
            201,
            ['X-CUSTOM-HEADER' => 'custom']
        );

        $next = static function (Request $request) use ($formattedApiResponse) {
            return $formattedApiResponse;
        };

        $response = (new ApiFormatsMiddleware($encoderGuesser, $psr7Factory))->handle($request, $next);

        self::assertSame(\json_encode($formattedApiResponse->getContent()), $response->getContent());
        self::assertSame($formattedApiResponse->getStatusCode(), $response->getStatusCode());
        self::assertTrue($response->headers->has('X-CUSTOM-HEADER'));
        self::assertSame('custom', $response->headers->get('X-CUSTOM-HEADER'));
    }

    /**
     * Middleware should return laravel response with right information if closure result is
     * formatted api response with serializable interface as content.
     *
     * @return void
     *
     * @throws \EoneoPay\ApiFormats\Bridge\Laravel\Exceptions\InvalidPsr7FactoryException
     * @throws \EoneoPay\ApiFormats\Exceptions\ApiFormatterException
     */
    public function testReturnRightFormattedResponseIfFormattedApiResponseWithSerializableInterface(): void
    {
        $psr7Factory = new Psr7Factory();
        $encoderGuesser = new EncoderGuesser([JsonEncoder::class => ['application/json']]);
        $request = $this->getRequest();
        $formattedApiResponse = new FormattedApiResponse(
            new SerializableInterfaceStub(),
            201,
            ['X-CUSTOM-HEADER' => 'custom']
        );

        $next = static function (Request $request) use ($formattedApiResponse) {
            return $formattedApiResponse;
        };

        $response = (new ApiFormatsMiddleware($encoderGuesser, $psr7Factory))->handle($request, $next);

        self::assertSame(\json_encode($formattedApiResponse->getContent()), $response->getContent());
        self::assertSame($formattedApiResponse->getStatusCode(), $response->getStatusCode());
        self::assertTrue($response->headers->has('X-CUSTOM-HEADER'));
        self::assertSame('custom', $response->headers->get('X-CUSTOM-HEADER'));
    }
}
