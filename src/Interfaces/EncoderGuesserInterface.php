<?php
declare(strict_types=1);

namespace EoneoPay\ApiFormats\Interfaces;

use Psr\Http\Message\ServerRequestInterface;

interface EncoderGuesserInterface
{
    /**
     * Get default encoder when formats configuration invalid.
     *
     * @param null|\Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \EoneoPay\ApiFormats\Interfaces\EncoderInterface
     */
    public function defaultEncoder(?ServerRequestInterface $request = null): EncoderInterface;

    /**
     * Guess encoder based on given request and given headers to check.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param null|string[] $headers
     *
     * @return \EoneoPay\ApiFormats\Interfaces\EncoderInterface
     *
     * @throws \EoneoPay\ApiFormats\Exceptions\UnsupportedRequestFormatException
     * @throws \EoneoPay\ApiFormats\Exceptions\InvalidEncoderException
     * @throws \EoneoPay\ApiFormats\Exceptions\InvalidSupportedRequestFormatsConfigException
     */
    public function guessEncoder(ServerRequestInterface $request, ?array $headers = null): EncoderInterface;

    /**
     * Guess request encoder based on given request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \EoneoPay\ApiFormats\Interfaces\EncoderInterface
     *
     * @throws \EoneoPay\ApiFormats\Exceptions\UnsupportedRequestFormatException
     * @throws \EoneoPay\ApiFormats\Exceptions\InvalidEncoderException
     * @throws \EoneoPay\ApiFormats\Exceptions\InvalidSupportedRequestFormatsConfigException
     */
    public function guessRequestEncoder(ServerRequestInterface $request): EncoderInterface;

    /**
     * Guess response encoder based on given request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \EoneoPay\ApiFormats\Interfaces\EncoderInterface
     *
     * @throws \EoneoPay\ApiFormats\Exceptions\UnsupportedRequestFormatException
     * @throws \EoneoPay\ApiFormats\Exceptions\InvalidEncoderException
     * @throws \EoneoPay\ApiFormats\Exceptions\InvalidSupportedRequestFormatsConfigException
     */
    public function guessResponseEncoder(ServerRequestInterface $request): EncoderInterface;
}
