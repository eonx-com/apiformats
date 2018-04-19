<?php
declare(strict_types=1);

namespace EoneoPay\ApiFormats\RequestEncoders;

use EoneoPay\Utils\Interfaces\SerializableInterface;
use Psr\Http\Message\ResponseInterface;

class UrlEncodedDataRequestEncoder extends AbstractRequestEncoder
{
    /**
     * Create response from given data, status code and headers.
     *
     * @param mixed $data
     * @param int|null $statusCode
     * @param array|null $headers
     *
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function encode($data, ?int $statusCode = null, ?array $headers = null): ResponseInterface
    {
        if ($data instanceof SerializableInterface) {
            $data = $data->toArray();
        }

        return $this->response(\rawurlencode(\http_build_query((array)$data)), $statusCode, $headers);
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
