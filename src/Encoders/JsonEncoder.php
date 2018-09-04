<?php
declare(strict_types=1);

namespace EoneoPay\ApiFormats\Encoders;

use Psr\Http\Message\ResponseInterface;

class JsonEncoder extends AbstractEncoder
{
    /**
     * Create response from given data, status code and headers.
     *
     * @param mixed $data
     * @param int|null $statusCode
     * @param string[]|null $headers
     *
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function encode($data, ?int $statusCode = null, ?array $headers = null): ResponseInterface
    {
        return $this->response(\json_encode($this->getDataAsArray($data)) ?: '', $statusCode, $headers);
    }

    /**
     * Decode request content to array.
     *
     * @param string $content
     *
     * @return mixed[]
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
