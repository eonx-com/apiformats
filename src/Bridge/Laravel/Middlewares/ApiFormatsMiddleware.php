<?php
declare(strict_types=1);

namespace EoneoPay\ApiFormats\Bridge\Laravel\Middlewares;

use Closure;
use EoneoPay\ApiFormats\Bridge\Laravel\Interfaces\ApiFormatsMiddlewareInterface;
use EoneoPay\ApiFormats\Bridge\Laravel\Traits\LaravelResponseTrait;
use EoneoPay\ApiFormats\External\Interfaces\Psr7FactoryInterface;
use EoneoPay\ApiFormats\Interfaces\FormattedApiResponseInterface;
use EoneoPay\ApiFormats\Interfaces\RequestEncoderGuesserInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Psr\Http\Message\ResponseInterface;

class ApiFormatsMiddleware implements ApiFormatsMiddlewareInterface
{
    use LaravelResponseTrait;

    /**
     * @var RequestEncoderGuesserInterface
     */
    private $encoderGuesser;

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
     * @throws \EoneoPay\ApiFormats\Bridge\Laravel\Exceptions\InvalidPsr7FactoryException
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
}
