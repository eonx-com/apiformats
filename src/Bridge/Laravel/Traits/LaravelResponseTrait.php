<?php
declare(strict_types=1);

namespace EoneoPay\ApiFormats\Bridge\Laravel\Traits;

use EoneoPay\ApiFormats\Bridge\Laravel\Exceptions\InvalidPsr7FactoryException;
use EoneoPay\ApiFormats\External\Interfaces\Psr7\Psr7FactoryInterface;
use Illuminate\Http\Response;
use Psr\Http\Message\ResponseInterface;

trait LaravelResponseTrait
{
    /**
     * @var \EoneoPay\ApiFormats\External\Interfaces\Psr7\Psr7FactoryInterface|null
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
        $symfonyResponse = $this->getPsr7Factory()->createResponse($response);

        // Remove content-length header as contents can change when Laravel creates response
        $symfonyResponse->headers->remove('content-length');

        return Response::create(
            $symfonyResponse->getContent(),
            $symfonyResponse->getStatusCode(),
            $symfonyResponse->headers->all()
        );
    }

    /**
     * Get the PSR7 factory instance
     *
     * @return \EoneoPay\ApiFormats\External\Interfaces\Psr7\Psr7FactoryInterface
     *
     * @throws \EoneoPay\ApiFormats\Bridge\Laravel\Exceptions\InvalidPsr7FactoryException
     */
    protected function getPsr7Factory(): Psr7FactoryInterface
    {
        if (($this->psr7Factory instanceof Psr7FactoryInterface) === true) {
            return $this->psr7Factory;
        }

        // Psr7 factory isn't set or incorrect, since this is a trait we really have no control over it
        throw new InvalidPsr7FactoryException(\sprintf(
            '%s::$psr7Factory value must implement %s to use %s',
            \get_class($this),
            Psr7FactoryInterface::class,
            __TRAIT__
        ));
    }
}
