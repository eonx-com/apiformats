<?php
declare(strict_types=1);

namespace EoneoPay\ApiFormats\RequestEncoders;

use EoneoPay\Utils\Interfaces\XmlConverterInterface;
use EoneoPay\Utils\XmlConverter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class XmlRequestEncoder extends AbstractRequestEncoder
{
    /**
     * @var \EoneoPay\Utils\Interfaces\XmlConverterInterface
     */
    private $xmlConverter;

    /**
     * XmlRequestEncoder constructor.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \EoneoPay\Utils\Interfaces\XmlConverterInterface|null $xmlConverter
     */
    public function __construct(ServerRequestInterface $request, XmlConverterInterface $xmlConverter = null)
    {
        parent::__construct($request);

        $this->xmlConverter = $xmlConverter ?? new XmlConverter();
    }

    /**
     * Decode request content to array.
     *
     * @return array
     *
     * @throws \RuntimeException
     * @throws \EoneoPay\Utils\Exceptions\InvalidXmlException
     */
    public function decode(): array
    {
        return $this->xmlConverter->xmlToArray($this->request->getBody()->getContents()) ?? [];
    }

    /**
     * Create response from given data, status code and headers.
     *
     * @param array $data
     * @param int|null $statusCode
     * @param array|null $headers
     *
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \EoneoPay\Utils\Exceptions\InvalidXmlTagException
     */
    public function encode(array $data, int $statusCode = null, array $headers = null): ResponseInterface
    {
        return $this->response($this->xmlConverter->arrayToXml($data) ?? '', $statusCode, $headers);
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
}
