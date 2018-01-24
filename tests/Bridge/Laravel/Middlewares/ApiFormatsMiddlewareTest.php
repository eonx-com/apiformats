<?php
declare(strict_types=1);

namespace Tests\EoneoPay\ApiFormats\Bridge\Laravel\Middlewares;

use EoneoPay\ApiFormats\Bridge\Laravel\Middlewares\ApiFormatsMiddleware;
use EoneoPay\ApiFormats\Bridge\Laravel\Responses\FormattedApiResponse;
use EoneoPay\ApiFormats\Exceptions\UnsupportedRequestFormatException;
use EoneoPay\ApiFormats\External\Libraries\Psr7Factory;
use EoneoPay\ApiFormats\RequestEncoderGuesser;
use EoneoPay\ApiFormats\RequestEncoders\JsonRequestEncoder;
use EoneoPay\ApiFormats\RequestEncoders\XmlRequestEncoder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\EoneoPay\ApiFormats\TestCases\BridgeLaravelMiddlewaresTestCase;

class ApiFormatsMiddlewareTest extends BridgeLaravelMiddlewaresTestCase
{
    /**
     * Middleware should set default encoder on request if exception thrown wile guessing encoder.
     */
    public function testDefaultEncoderOnRequestIfException(): void
    {
        $this->expectException(UnsupportedRequestFormatException::class);

        $psr7Factory = new Psr7Factory();
        $encoderGuesser = new RequestEncoderGuesser([JsonRequestEncoder::class => ['application/json']]);
        $request = $this->getRequest(null, ['accept' => 'invalid']);

        (new ApiFormatsMiddleware($encoderGuesser, $psr7Factory))->handle($request, function () {
        });

        self::assertInstanceOf(JsonRequestEncoder::class, $request->attributes->get('_encoder'));
    }

    /**
     * Middleware should set the right encoder on the request, replace inputs on the request and return
     * an Laravel response.
     */
    public function testHandleFormatsProperly(): void
    {
        $formats = [
            JsonRequestEncoder::class => [
                'mime_type' => 'application/json',
                'content' => '{"email":"email@eoneopay.com.au"}'
            ],
            XmlRequestEncoder::class => [
                'mime_type' => 'application/xml',
                'content' => '<data><email>email@eoneopay.com.au</email></data>'
            ]
        ];

        $middleware = new ApiFormatsMiddleware(new RequestEncoderGuesser($formats), new Psr7Factory());
        $next = function (Request $request) {
            return $request->all();
        };

        foreach ($formats as $encoder => $test) {
            $request = $this->getRequest($test['content'], ['accept' => $test['mime_type']]);
            $response = $middleware->handle($request, $next);

            self::assertInstanceOf($encoder, $request->attributes->get('_encoder'));
            self::assertInstanceOf(Response::class, $response);
            /** @var Response $response */
            self::assertEquals($test['mime_type'], $response->headers->get('Content-Type'));

            switch ($encoder) {
                case JsonRequestEncoder::class:
                    self::assertEquals($test['content'], $response->getContent());
                    break;
                case XmlRequestEncoder::class:
                    self::assertXmlStringEqualsXmlString($test['content'], $response->getContent());
                    break;
            }
        }
    }

    /**
     * Middleware should return directly response if closure result is a laravel response.
     */
    public function testReturnDirectlyResponseIfAlreadyLaravelOne(): void
    {
        $psr7Factory = new Psr7Factory();
        $encoderGuesser = new RequestEncoderGuesser([JsonRequestEncoder::class => ['application/json']]);
        $request = $this->getRequest();
        $laravelResponse = new Response();

        $next = function (Request $request) use ($laravelResponse) {
            return $laravelResponse;
        };

        $response = (new ApiFormatsMiddleware($encoderGuesser, $psr7Factory))->handle($request, $next);

        self::assertEquals(\spl_object_hash($laravelResponse), \spl_object_hash($response));
    }

    /**
     * Middleware should return laravel response with right information if closure result is
     * formatter api response.
     */
    public function testReturnRightFormattedResponseIfFormattedApiResponse(): void
    {
        $psr7Factory = new Psr7Factory();
        $encoderGuesser = new RequestEncoderGuesser([JsonRequestEncoder::class => ['application/json']]);
        $request = $this->getRequest();
        $formattedApiResponse = new FormattedApiResponse(
            ['email' => 'email@eoneopay.com.au'],
            201,
            ['X-CUSTOM-HEADER' => 'custom']
        );

        $next = function (Request $request) use ($formattedApiResponse) {
            return $formattedApiResponse;
        };

        $response = (new ApiFormatsMiddleware($encoderGuesser, $psr7Factory))->handle($request, $next);

        self::assertEquals(\json_encode($formattedApiResponse->getContent()), $response->getContent());
        self::assertEquals($formattedApiResponse->getStatusCode(), $response->getStatusCode());
        self::assertTrue($response->headers->has('X-CUSTOM-HEADER'));
        self::assertEquals('custom', $response->headers->get('X-CUSTOM-HEADER'));
    }
}
