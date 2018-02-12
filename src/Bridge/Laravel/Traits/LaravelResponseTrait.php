<?php
declare(strict_types=1);

namespace EoneoPay\ApiFormats\Bridge\Laravel\Traits;

use EoneoPay\ApiFormats\Bridge\Laravel\Exceptions\InvalidPsr7FactoryException;
use EoneoPay\ApiFormats\External\Interfaces\Psr7FactoryInterface;
use Illuminate\Http\Response;
use Psr\Http\Message\ResponseInterface;

trait LaravelResponseTrait
{
    /**
     * @var \EoneoPay\ApiFormats\External\Interfaces\Psr7FactoryInterface
     */
    private $psr7Factory;

    /**
     * Create Laravel response from PSR-7 response.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \EoneoPay\ApiFormats\Bridge\Laravel\Exceptions\InvalidPsr7FactoryException
     *
     * @SuppressWarnings(PHPMD.StaticAccess) Laravel way to create response
     */
    protected function createLaravelResponseFromPsr(ResponseInterface $response): Response
    {
        if (!$this->psr7Factory instanceof Psr7FactoryInterface) {
            throw new InvalidPsr7FactoryException(\sprintf(
                '%s::$psr7Factory value must implement %s to use %s',
                \get_class($this),
                Psr7FactoryInterface::class,
                __TRAIT__
            ));
        }

        $symfonyResponse = $this->psr7Factory->createResponse($response);

        return Response::create(
            $symfonyResponse->getContent(),
            $symfonyResponse->getStatusCode(),
            $symfonyResponse->headers->all()
        );
    }
}
