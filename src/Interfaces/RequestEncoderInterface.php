<?php
declare(strict_types = 1);

namespace EoneoPay\ApiFormats\Interfaces;

use Psr\Http\Message\ResponseInterface;

interface RequestEncoderInterface
{
    /**
     * Create response from given data, status code and headers.
     *
     * @param mixed $data
     * @param int|null $statusCode
     * @param array|null $headers
     *
     * @return ResponseInterface
     */
    public function encode($data, ?int $statusCode = null, ?array $headers = null): ResponseInterface;

    /**
     * Decode request content to array.
     *
     * @return array
     */
    public function decode(): array;

    /**
     * Manually set content to decode.
     *
     * @param string $content
     *
     * @return \EoneoPay\ApiFormats\Interfaces\RequestEncoderInterface
     */
    public function setContent(string $content): self;
}
