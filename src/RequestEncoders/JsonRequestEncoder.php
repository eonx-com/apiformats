<?php
declare(strict_types = 1);

namespace EoneoPay\ApiFormats\RequestEncoders;

use Psr\Http\Message\ResponseInterface;

class JsonRequestEncoder extends AbstractRequestEncoder
{
    /**
     * Decode request content to array.
     *
     * @return array
     *
     * @throws \RuntimeException
     * @throws \LogicException
     */
    public function decode(): array
    {
        return \json_decode($this->request->getBody()->getContents(), true) ?? [];
    }

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
     * Returns HTTP Content-Type header value.
     *
     * @return string
     */
    protected function getContentTypeHeader(): string
    {
        return 'application/json';
    }
}
