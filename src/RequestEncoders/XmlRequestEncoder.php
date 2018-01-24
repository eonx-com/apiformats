<?php
declare(strict_types=1);

namespace EoneoPay\ApiFormats\RequestEncoders;

use DOMDocument;
use DOMNode;
use EoneoPay\ApiFormats\Exceptions\InvalidDecodingContentException;
use Psr\Http\Message\ResponseInterface;

class XmlRequestEncoder extends AbstractRequestEncoder
{
    private const ENCODING = 'UTF-8';

    private const ROOT_NODE = 'data';

    private const VERSION = '1.0';

    /**
     * XML DOMDocument
     *
     * @var \DOMDocument
     */
    private $xml;

    /**
     * Decode request content to array.
     *
     * @return array
     *
     * @throws \RuntimeException
     * @throws \LogicException
     * @throws \EoneoPay\ApiFormats\Exceptions\InvalidDecodingContentException
     */
    public function decode(): array
    {
        // Load xml without escaping CDATA
        $xml = \simplexml_load_string(
            $this->request->getBody()->getContents(),
            'SimpleXMLElement',
            LIBXML_NOCDATA
        );

        // If xml failed to load, throw exception
        if ($xml === false) {
            throw new InvalidDecodingContentException('The xml passed to xmlToArray was not valid');
        }

        // Encode and decode to convert to array
        $array = \json_decode(\json_encode($xml), true);

        // The encode/decode works mostly however self closing tags are converted to empty
        // arrays so they need to be recursively converted into strings
        $array = $this->convertEmptyArraysToString($array);

        // Force an array to be returned
        return \is_array($array) ? $array : [];
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
     * @throws \Exception
     */
    public function encode(array $data, int $statusCode = null, array $headers = null): ResponseInterface
    {
        $this->xml = new DOMDocument(self::VERSION, self::ENCODING);
        $this->xml->formatOutput = true;
        $this->xml->appendChild($this->createXmlNode(self::ROOT_NODE, $data));

        return $this->response($this->xml->saveXML(), $statusCode, $headers);
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
     * Append an xml attribute from an array or value
     *
     * @param DOMNode $node The node to add the value to
     * @param string $name The node name to add
     * @param mixed $value The value to attach to the node
     *
     * @return DOMNode The updated node
     *
     * @throws \EoneoPay\ApiFormats\Exceptions\InvalidDecodingContentException
     */
    private function appendXmlAttribute(DOMNode $node, string $name, $value): DOMNode
    {
        // If value is an array, recurse
        if (\is_array($value) && \is_numeric(key($value))) {
            // If key is numeric it's multiple types of the same thing, attach to parent
            foreach ($value as $childValue) {
                $node->appendChild($this->createXmlNode($name, $childValue));
            }

            return $node;
        }

        // Add value and return
        $node->appendChild($this->createXmlNode($name, $value));

        return $node;
    }

    /**
     * Recursively convert empty arrays to strings
     *
     * @param array $array The array to convert
     * @param string $replacement The string to replace empty arrays with
     *
     * @return array The converted array
     */
    private function convertEmptyArraysToString(array $array, string $replacement = null): array
    {
        $replacement = $replacement ?? '';

        foreach ($array as $key => $value) {
            // Ignore non-arrays
            if (!\is_array($value)) {
                continue;
            }

            // If value is an empty array, replace
            if (\is_array($value) && \count($value) === 0) {
                $array[$key] = $replacement;
                continue;
            }

            // Recurse
            $array[$key] = $this->convertEmptyArraysToString($value, $replacement);
        }

        return $array;
    }

    /**
     * Create an XML node from an array, recursively
     *
     * @param string $nodeName The name of the node to convert this array to
     * @param mixed $nodeValue The value to add, can be array or scalar value
     *
     * @return \DOMNode The created node ready to append to a parent
     *
     * @throws \EoneoPay\ApiFormats\Exceptions\InvalidDecodingContentException
     */
    private function createXmlNode(string $nodeName, $nodeValue): DOMNode
    {
        // Create the node
        $node = $this->xml->createElement($nodeName);

        // If value is an array, attempt to process attributes and values
        if (\is_array($nodeValue)) {
            // Process attributes
            if (isset($nodeValue['@attributes'])) {
                foreach ((array) $nodeValue['@attributes'] as $key => $value) {
                    // Ensure the attribute key is valid
                    if (!$this->isValidXmlTag($key)) {
                        throw new InvalidDecodingContentException(\sprintf(
                            'Attribute name is invalid for "%s" in node "%s"',
                            $key,
                            $nodeName
                        ));
                    }

                    $node->setAttribute($key, $this->xToString($value));
                }

                // Remove attributes array
                unset($nodeValue['@attributes']);
            }

            // Set values directly
            if (isset($nodeValue['@value'])) {
                $node->appendChild($this->xml->createTextNode($this->xToString($nodeValue['@value'])));

                // Remove value from array
                unset($nodeValue['@value']);

                // If there was a value, there is no recursion
                return $node;
            }

            // Set cname directly
            if (isset($nodeValue['@cdata'])) {
                $node->appendChild($this->xml->createCDATASection($this->xToString($nodeValue['@cdata'])));

                // Remove cdata from array
                unset($nodeValue['@cdata']);

                // If there was cdata, there is no recursion
                return $node;
            }

            /** @var array $nodeValue */
            foreach ($nodeValue as $key => $value) {
                // Ensure node name is valid
                if (!$this->isValidXmlTag($key)) {
                    throw new InvalidDecodingContentException(\sprintf(
                        'Node name is invalid for "%s" in node "%s"',
                        $key,
                        $nodeName
                    ));
                }

                // Process node
                $node = $this->appendXmlAttribute($node, $key, $value);

                // Remove array key to prevent double processing
                unset($nodeValue[$key]);
            }

            // Nothing further to process, return
            return $node;
        }

        // If node isn't an array, add it directly
        $node->appendChild($this->xml->createTextNode($this->xToString($nodeValue)));

        return $node;
    }

    /**
     * Ensure the node name or attribute only contains valid characters
     *
     * @param string $name The name to validate
     *
     * @return bool Whether the tag is valid for XML or not
     */
    private function isValidXmlTag(string $name): bool
    {
        $pattern = '/^[a-z_]+[a-z0-9\:\-\.\_]*[^:]*$/i';

        return \preg_match($pattern, $name, $matches) && \reset($matches) === $name;
    }

    /**
     * Convert a value to a string
     *
     * @param mixed $value The value to convert
     *
     * @return string The string representation of the value
     */
    private function xToString($value): string
    {
        // Convert booleans to string true/false as (string) converts to 1/0
        if (\is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        // Cast to string
        return (string) $value;
    }
}
