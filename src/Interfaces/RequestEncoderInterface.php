<?php
declare(strict_types = 1);

namespace EoneoPay\ApiFormats\Interfaces;

use Psr\Http\Message\ResponseInterface;

interface RequestEncoderInterface
{
    /**
     * Create response from given data, status code and headers.
     *
     * @param array      $data
     * @param int|null   $statusCode
     * @param array|null $headers
     *
     * @return ResponseInterface
     */
    public function encode(array $data, int $statusCode = null, array $headers = null): ResponseInterface;

    /**
     * Decode request content to array.
     *
     * @return array
     */
    public function decode(): array;
}
