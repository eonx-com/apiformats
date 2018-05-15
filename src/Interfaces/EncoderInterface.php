<?php
declare(strict_types=1);

namespace EoneoPay\ApiFormats\Interfaces;

use Psr\Http\Message\ResponseInterface;

interface EncoderInterface
{
    /**
     * Decode request content to array.
     *
     * @return mixed[]
     */
    public function decode(): array;

    /**
     * Create response from given data, status code and headers.
     *
     * @param mixed $data
     * @param int|null $statusCode
     * @param string[]|null $headers
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function encode($data, ?int $statusCode = null, ?array $headers = null): ResponseInterface;

    /**
     * Manually set content to decode.
     *
     * @param string $content
     *
     * @return \EoneoPay\ApiFormats\Interfaces\EncoderInterface
     */
    public function setContent(string $content): self;
}
