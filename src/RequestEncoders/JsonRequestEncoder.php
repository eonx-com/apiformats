<?php
declare(strict_types=1);

namespace EoneoPay\ApiFormats\RequestEncoders;

use Psr\Http\Message\ResponseInterface;

class JsonRequestEncoder extends AbstractRequestEncoder
{
    /**
     * Create response from given data, status code and headers.
     *
     * @param array $data
     * @param int|null $statusCode
     * @param array|null $headers
     *
     * @return ResponseInterface
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function encode(array $data, int $statusCode = null, array $headers = null): ResponseInterface
    {
        return $this->response(\json_encode($data), $statusCode, $headers);
    }

    /**
     * Decode request content to array.
     *
     * @param string $content
     *
     * @return array
     */
    protected function decodeRequestContent(string $content): array
    {
        return \json_decode($content, true) ?? [];
    }

    /**
     * Returns HTTP Content-Type header value.
     *
     * @return string
     */
    protected function getContentTypeHeader(): string
    {
        return 'application/json';
    }
}
