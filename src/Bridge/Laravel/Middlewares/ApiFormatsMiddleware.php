<?php
declare(strict_types=1);

namespace EoneoPay\ApiFormats\Bridge\Laravel\Middlewares;

use Closure;
use EoneoPay\ApiFormats\Bridge\Laravel\Interfaces\ApiFormatsMiddlewareInterface;
use EoneoPay\ApiFormats\External\Interfaces\Psr7FactoryInterface;
use EoneoPay\ApiFormats\Interfaces\FormattedApiResponseInterface;
use EoneoPay\ApiFormats\Interfaces\RequestEncoderGuesserInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Psr\Http\Message\ResponseInterface;

class ApiFormatsMiddleware implements ApiFormatsMiddlewareInterface
{
    /**
     * @var RequestEncoderGuesserInterface
     */
    private $encoderGuesser;

    /**
     * @var Psr7FactoryInterface
     */
    private $psr7Factory;

    /**
     * ApiFormatsMiddleware constructor.
     *
     * @param \EoneoPay\ApiFormats\Interfaces\RequestEncoderGuesserInterface $encoderGuesser
     * @param \EoneoPay\ApiFormats\External\Interfaces\Psr7FactoryInterface $psr7Factory
     */
    public function __construct(RequestEncoderGuesserInterface $encoderGuesser, Psr7FactoryInterface $psr7Factory)
    {
        $this->encoderGuesser = $encoderGuesser;
        $this->psr7Factory = $psr7Factory;
    }

    /**
     * Handle incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     *
     * @throws \Exception
     * @throws \EoneoPay\ApiFormats\Exceptions\UnsupportedRequestFormatException
     * @throws \EoneoPay\ApiFormats\Exceptions\InvalidSupportedRequestFormatsConfigException
     */
    public function handle(Request $request, Closure $next)
    {
        $psr7Request = $this->psr7Factory->createRequest($request);

        try {
            $encoder = $this->encoderGuesser->guessEncoder($psr7Request);
        } catch (Exception $exception) {
            $request->attributes->set('_encoder', $this->encoderGuesser->defaultEncoder($psr7Request));
            throw $exception;
        }

        $request->attributes->set('_encoder', $encoder);
        $request->replace($encoder->decode());

        $response = $next($request);

        if ($response instanceof FormattedApiResponseInterface) {
            return $this->laravelResponse($encoder->encode(
                (array) $response->getContent(),
                $response->getStatusCode(),
                $response->getHeaders()
            ));
        }

        if ($response instanceof Response) {
            return $response;
        }

        return $this->laravelResponse($encoder->encode((array) $response));
    }

    /**
     * Create Laravel response from PSR-7 response.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return \Illuminate\Http\Response
     *
     * @SuppressWarnings(PHPMD.StaticAccess) Laravel way to create response
     */
    private function laravelResponse(ResponseInterface $response): Response
    {
        $symfonyResponse = $this->psr7Factory->createResponse($response);

        return Response::create(
            $symfonyResponse->getContent(),
            $symfonyResponse->getStatusCode(),
            $symfonyResponse->headers->all()
        );
    }
}
