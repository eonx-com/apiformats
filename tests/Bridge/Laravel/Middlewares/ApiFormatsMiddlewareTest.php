<?php
declare(strict_types=1);

namespace Tests\EoneoPay\ApiFormats\Bridge\Laravel\Middlewares;

use EoneoPay\ApiFormats\Bridge\Laravel\Middlewares\ApiFormatsMiddleware;
use EoneoPay\ApiFormats\Bridge\Laravel\Responses\FormattedApiResponse;
use EoneoPay\ApiFormats\Bridge\Laravel\Responses\NoContentApiResponse;
use EoneoPay\ApiFormats\EncoderGuesser;
use EoneoPay\ApiFormats\Encoders\JsonEncoder;
use EoneoPay\ApiFormats\Encoders\XmlEncoder;
use EoneoPay\ApiFormats\Exceptions\UnsupportedAcceptHeaderException;
use EoneoPay\ApiFormats\Exceptions\UnsupportedContentTypeHeaderException;
use EoneoPay\ApiFormats\External\Libraries\Psr7\Psr7Factory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laminas\Diactoros\Response as PsrResponse;
use Laminas\Diactoros\StreamFactory;
use Tests\EoneoPay\ApiFormats\Stubs\SerializableInterfaceStub;
use Tests\EoneoPay\ApiFormats\TestCases\BridgeLaravelMiddlewaresTestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) High coupling required due to different formats
 */
class ApiFormatsMiddlewareTest extends BridgeLaravelMiddlewaresTestCase
{
    /**
     * Middleware should thrown an exception if it encounters a valid header with an invalid value
     *
     * @return void
     *
     * @throws \EoneoPay\ApiFormats\Bridge\Laravel\Exceptions\InvalidPsr7FactoryException
     * @throws \EoneoPay\ApiFormats\Exceptions\ApiFormatterException
     */
    public function testExceptionThrownIfRequestHeaderInvalid(): void
    {
        $this->expectException(UnsupportedContentTypeHeaderException::class);

        $psr7Factory = new Psr7Factory();
        $encoderGuesser = new EncoderGuesser([JsonEncoder::class => ['application/json']]);
        $request = $this->getRequest(null, ['accept' => 'application/json', 'content-type' => 'invalid']);

        $instance = new ApiFormatsMiddleware($encoderGuesser, $psr7Factory);

        $instance->handle($request, function (): void {
        });
    }

    /**
     * Middleware should thrown an exception if it encounters a valid header with an invalid value
     *
     * @return void
     *
     * @throws \EoneoPay\ApiFormats\Bridge\Laravel\Exceptions\InvalidPsr7FactoryException
     * @throws \EoneoPay\ApiFormats\Exceptions\ApiFormatterException
     */
    public function testExceptionThrownIfResponseHeaderInvalid(): void
    {
        $this->expectException(UnsupportedAcceptHeaderException::class);

        $psr7Factory = new Psr7Factory();
        $encoderGuesser = new EncoderGuesser([JsonEncoder::class => ['application/json']]);
        $request = $this->getRequest(null, ['accept' => 'invalid', 'content-type' => 'application/json']);

        $instance = new ApiFormatsMiddleware($encoderGuesser, $psr7Factory);

        $instance->handle($request, function (): void {
        });
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
        $next = static function (Request $request): array {
            return $request->all();
        };

        foreach ($formats as $encoder => $test) {
            $request = $this->getRequest($test['content'], ['accept' => $test['mime_type']]);
            $response = $middleware->handle($request, $next);

            self::assertInstanceOf($encoder, $request->attributes->get('_encoder'));
            self::assertInstanceOf(Response::class, $response);
            /** @var \Illuminate\Http\Response $response */
            self::assertSame($test['mime_type'], $response->headers->get('Content-Type'));

            $content = $response->getContent();
            $content = $content === false ? '' : $content;

            switch ($encoder) {
                case JsonEncoder::class:
                    self::assertSame($test['content'], $content);
                    break;

                case XmlEncoder::class:
                    self::assertXmlStringEqualsXmlString($test['content'], $content);
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

        $next = static function (Request $request) use ($emptyResponse): NoContentApiResponse {
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

        $next = static function (Request $request) use ($laravelResponse): Response {
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

        $next = static function (Request $request) use ($formattedApiResponse): FormattedApiResponse {
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

        $next = static function (Request $request) use ($formattedApiResponse): FormattedApiResponse {
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
    public function testReturningPsrResponseDirectly(): void
    {
        $psr7Factory = new Psr7Factory();
        $encoderGuesser = new EncoderGuesser([JsonEncoder::class => ['application/json']]);
        $request = $this->getRequest();

        $body = (new StreamFactory())->createStream('body');
        $psrResponse = new PsrResponse($body, 201, ['X-CUSTOM-HEADER' => 'custom']);

        $next = static function (Request $request) use ($psrResponse): \Laminas\Diactoros\Response {
            return $psrResponse;
        };

        $response = (new ApiFormatsMiddleware($encoderGuesser, $psr7Factory))->handle($request, $next);

        self::assertSame('body', $response->getContent());
        self::assertSame(201, $response->getStatusCode());
        self::assertTrue($response->headers->has('X-CUSTOM-HEADER'));
        self::assertSame('custom', $response->headers->get('X-CUSTOM-HEADER'));
    }
}
