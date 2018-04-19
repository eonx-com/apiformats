<?php
declare(strict_types=1);

namespace EoneoPay\ApiFormats\Interfaces;

use Psr\Http\Message\ServerRequestInterface;

interface RequestEncoderGuesserInterface
{
    /**
     * Get default encoder when formats configuration invalid.
     *
     * @param null|ServerRequestInterface $request
     *
     * @return RequestEncoderInterface
     */
    public function defaultEncoder(?ServerRequestInterface $request = null): RequestEncoderInterface;

    /**
     * Guess encoder based on given request and given headers to check.
     *
     * @param ServerRequestInterface $request
     * @param null|array $headers
     *
     * @return RequestEncoderInterface
     *
     * @throws \EoneoPay\ApiFormats\Exceptions\UnsupportedRequestFormatException
     * @throws \EoneoPay\ApiFormats\Exceptions\InvalidEncoderException
     * @throws \EoneoPay\ApiFormats\Exceptions\InvalidSupportedRequestFormatsConfigException
     */
    public function guessEncoder(ServerRequestInterface $request, ?array $headers = null): RequestEncoderInterface;

    /**
     * Guess request encoder based on given request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \EoneoPay\ApiFormats\Interfaces\RequestEncoderInterface
     *
     * @throws \EoneoPay\ApiFormats\Exceptions\UnsupportedRequestFormatException
     * @throws \EoneoPay\ApiFormats\Exceptions\InvalidEncoderException
     * @throws \EoneoPay\ApiFormats\Exceptions\InvalidSupportedRequestFormatsConfigException
     */
    public function guessRequestEncoder(ServerRequestInterface $request): RequestEncoderInterface;

    /**
     * Guess response encoder based on given request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \EoneoPay\ApiFormats\Interfaces\RequestEncoderInterface
     *
     * @throws \EoneoPay\ApiFormats\Exceptions\UnsupportedRequestFormatException
     * @throws \EoneoPay\ApiFormats\Exceptions\InvalidEncoderException
     * @throws \EoneoPay\ApiFormats\Exceptions\InvalidSupportedRequestFormatsConfigException
     */
    public function guessResponseEncoder(ServerRequestInterface $request): RequestEncoderInterface;
}
