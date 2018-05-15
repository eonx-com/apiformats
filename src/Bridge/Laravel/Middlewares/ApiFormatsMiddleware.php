<?php
declare(strict_types=1);

namespace EoneoPay\ApiFormats\Bridge\Laravel\Middlewares;

use Closure;
use EoneoPay\ApiFormats\Bridge\Laravel\Interfaces\ApiFormatsMiddlewareInterface;
use EoneoPay\ApiFormats\Bridge\Laravel\Traits\LaravelResponseTrait;
use EoneoPay\ApiFormats\External\Interfaces\Psr7\Psr7FactoryInterface;
use EoneoPay\ApiFormats\Interfaces\EncoderGuesserInterface;
use EoneoPay\ApiFormats\Interfaces\FormattedApiResponseInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\ParameterBag;

class ApiFormatsMiddleware implements ApiFormatsMiddlewareInterface
{
    use LaravelResponseTrait;

    /**
     * @var \EoneoPay\ApiFormats\Interfaces\EncoderGuesserInterface
     */
    private $encoderGuesser;

    /**
     * ApiFormatsMiddleware constructor.
     *
     * @param \EoneoPay\ApiFormats\Interfaces\EncoderGuesserInterface $encoderGuesser
     * @param \EoneoPay\ApiFormats\External\Interfaces\Psr7\Psr7FactoryInterface $psr7Factory
     */
    public function __construct(EncoderGuesserInterface $encoderGuesser, Psr7FactoryInterface $psr7Factory)
    {
        $this->encoderGuesser = $encoderGuesser;
        $this->psr7Factory = $psr7Factory;
    }

    /**
     * Handle incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     *
     * @throws \EoneoPay\ApiFormats\Bridge\Laravel\Exceptions\InvalidPsr7FactoryException
     * @throws \Exception
     */
    public function handle(Request $request, Closure $next)
    {
        $psr7Request = $this->psr7Factory->createRequest($request);

        try {
            $requestEncoder = $this->encoderGuesser->guessRequestEncoder($psr7Request);
            $responseEncoder = $this->encoderGuesser->guessResponseEncoder($psr7Request);
        } catch (Exception $exception) {
            $request->attributes->set('_encoder', $this->encoderGuesser->defaultEncoder($psr7Request));
            throw $exception;
        }

        $request->attributes->set('_encoder', $responseEncoder);
        $request->request = new ParameterBag($requestEncoder->decode());

        $response = $next($request);

        if ($response instanceof FormattedApiResponseInterface) {
            return $this->createLaravelResponseFromPsr($responseEncoder->encode(
                $response->getContent(),
                $response->getStatusCode(),
                $response->getHeaders()
            ));
        }

        if ($response instanceof Response) {
            return $response;
        }

        return $this->createLaravelResponseFromPsr($responseEncoder->encode((array)$response));
    }
}
