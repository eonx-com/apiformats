<?php
declare(strict_types=1);

namespace EoneoPay\ApiFormats\External\Libraries;

use EoneoPay\ApiFormats\External\Interfaces\Psr7FactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\HttpFoundationFactoryInterface;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Psr7Factory implements Psr7FactoryInterface
{
    /**
     * @var \Symfony\Bridge\PsrHttpMessage\HttpFoundationFactoryInterface
     */
    private $httpFoundation;

    /**
     * @var \Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface
     */
    private $httpMessage;

    /**
     * Psr7Factory constructor.
     *
     * @param \Symfony\Bridge\PsrHttpMessage\HttpFoundationFactoryInterface|null $httpFoundationFactory
     * @param \Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface|null $httpMessageFactory
     */
    public function __construct(
        HttpFoundationFactoryInterface $httpFoundationFactory = null,
        HttpMessageFactoryInterface $httpMessageFactory = null
    ) {
        $this->httpFoundation = $httpFoundationFactory ?? new HttpFoundationFactory();
        $this->httpMessage = $httpMessageFactory ?? new DiactorosFactory();
    }

    /**
     * Create PSR-7 request from Symfony request.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    public function createRequest(Request $request): ServerRequestInterface
    {
        return $this->httpMessage->createRequest($request);
    }

    /**
     * Create Symfony response from PSR-7 response.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createResponse(ResponseInterface $response): Response
    {
        return $this->httpFoundation->createResponse($response);
    }
}
