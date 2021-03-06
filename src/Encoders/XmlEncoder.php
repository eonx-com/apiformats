<?php
declare(strict_types=1);

namespace EoneoPay\ApiFormats\Encoders;

use EoneoPay\Utils\Interfaces\XmlConverterInterface;
use EoneoPay\Utils\XmlConverter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class XmlEncoder extends AbstractEncoder
{
    /**
     * @var \EoneoPay\Utils\Interfaces\XmlConverterInterface
     */
    private $xmlConverter;

    /**
     * XmlEncoder constructor.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \EoneoPay\Utils\Interfaces\XmlConverterInterface|null $xmlConverter
     */
    public function __construct(?ServerRequestInterface $request = null, ?XmlConverterInterface $xmlConverter = null)
    {
        parent::__construct($request);

        $this->xmlConverter = $xmlConverter ?? new XmlConverter();
    }

    /**
     * Create response from given data, status code and headers.
     *
     * @param mixed $data
     * @param int|null $statusCode
     * @param string[]|null $headers
     *
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @throws \ReflectionException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \EoneoPay\Utils\Exceptions\InvalidXmlTagException
     */
    public function encode($data, ?int $statusCode = null, ?array $headers = null): ResponseInterface
    {
        $data = $this->getDataAsArray($data);

        if ($this->isCollection($data)) {
            $data = $this->collectionToArray($data);
        }

        return $this->response($this->xmlConverter->arrayToXml($data) ?? '', $statusCode, $headers);
    }

    /**
     * Decode request content to array.
     *
     * @param string $content
     *
     * @return mixed[]
     *
     * @throws \EoneoPay\Utils\Exceptions\InvalidXmlException
     */
    protected function decodeRequestContent(string $content): array
    {
        return $this->xmlConverter->xmlToArray($content) ?? [];
    }

    /**
     * Returns HTTP Content-Type header value.
     *
     * @return string
     */
    protected function getContentTypeHeader(): string
    {
        return 'application/xml';
    }

    /**
     * Convert collection to array.
     *
     * @param mixed[] $data
     *
     * @return mixed[]
     *
     * @throws \ReflectionException
     */
    private function collectionToArray(array $data): array
    {
        $array = [];

        foreach ($data as $item) {
            $array[] = $this->getDataAsArray($item);
        }

        return [$this->getResourceKey($data) => $array];
    }
}
