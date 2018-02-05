<?php
declare(strict_types = 1);

namespace EoneoPay\ApiFormats\Interfaces;

use EoneoPay\ApiFormats\Exceptions\InvalidSupportedRequestFormatsConfigException;
use EoneoPay\ApiFormats\Exceptions\UnsupportedRequestFormatException;
use Psr\Http\Message\ServerRequestInterface;

interface RequestEncoderGuesserInterface
{
    /**
     * Guess encoder based on given request.
     *
     * @param ServerRequestInterface $request
     *
     * @return RequestEncoderInterface
     *
     * @throws UnsupportedRequestFormatException
     * @throws InvalidSupportedRequestFormatsConfigException
     */
    public function guessEncoder(ServerRequestInterface $request): RequestEncoderInterface;

    /**
     * Get default encoder when formats configuration invalid.
     *
     * @param null|ServerRequestInterface $request
     *
     * @return RequestEncoderInterface
     */
    public function defaultEncoder(?ServerRequestInterface $request = null): RequestEncoderInterface;
}
