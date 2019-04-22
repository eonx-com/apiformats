<?php
declare(strict_types=1);

namespace EoneoPay\ApiFormats\Bridge\Laravel\Middlewares;

use Closure;
use EoneoPay\ApiFormats\Bridge\Laravel\Interfaces\ApiFormatsMiddlewareInterface;
use EoneoPay\ApiFormats\Bridge\Laravel\Responses\NoContentApiResponse;
use EoneoPay\ApiFormats\Bridge\Laravel\Traits\LaravelResponseTrait;
use EoneoPay\ApiFormats\Exceptions\ApiFormatterException;
use EoneoPay\ApiFormats\External\Interfaces\Psr7\Psr7FactoryInterface;
use EoneoPay\ApiFormats\Interfaces\EncoderGuesserInterface;
use EoneoPay\ApiFormats\Interfaces\FormattedApiResponseInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Psr\Http\Message\ResponseInterface;
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
     * @throws \EoneoPay\ApiFormats\Exceptions\ApiFormatterException
     */
    public function handle(Request $request, Closure $next)
    {
        $psr7Request = $this->getPsr7Factory()->createRequest($request);

        try {
            $requestEncoder = $this->encoderGuesser->guessRequestEncoder($psr7Request);
            $responseEncoder = $this->encoderGuesser->guessResponseEncoder($psr7Request);
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (ApiFormatterException $exception) {
            $request->attributes->set('_encoder', $this->encoderGuesser->defaultEncoder($psr7Request));

            // Wrap exception
            throw $exception;
        }

        $request->attributes->set('_encoder', $responseEncoder);
        $request->request = new ParameterBag($requestEncoder->decode());

        $response = $next($request);

        if (($response instanceof ResponseInterface) === true) {
            return $this->createLaravelResponseFromPsr($response);
        }

        if (($response instanceof FormattedApiResponseInterface) === true) {
            return ($response instanceof NoContentApiResponse) === true ?
                $response :
                $this->createLaravelResponseFromPsr($responseEncoder->encode(
                    $response->getContent(),
                    $response->getStatusCode(),
                    $response->getHeaders()
                ));
        }

        if (($response instanceof Response) === true
            || ($response instanceof JsonResponse) === true
            || ($response instanceof RedirectResponse) === true) {
            return $response;
        }

        return $this->createLaravelResponseFromPsr($responseEncoder->encode((array)$response));
    }
}
