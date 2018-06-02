<?php
declare(strict_types=1);

namespace EoneoPay\ApiFormats\Encoders;

use EoneoPay\Utils\Interfaces\SerializableInterface;
use Psr\Http\Message\ResponseInterface;

class UrlEncodedDataEncoder extends AbstractEncoder
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
        return $this->response(\rawurlencode(\http_build_query($this->getDataAsArray($data))), $statusCode, $headers);
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
        \parse_str($content, $decoded);

        return \count($decoded) ? $decoded : [];
    }

    /**
     * Returns HTTP Content-Type header value.
     *
     * @return string
     */
    protected function getContentTypeHeader(): string
    {
        return 'application/x-www-form-urlencoded';
    }
}
