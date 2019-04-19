<?php
declare(strict_types=1);

namespace EoneoPay\ApiFormats\Encoders;

use Doctrine\Common\Inflector\Inflector;
use EoneoPay\ApiFormats\Exceptions\DecodeNullRequestException;
use EoneoPay\ApiFormats\Interfaces\EncoderInterface;
use EoneoPay\Utils\Interfaces\SerializableInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionClass;
use Zend\Diactoros\Response;
use Zend\Diactoros\Stream;

abstract class AbstractEncoder implements EncoderInterface
{
    /**
     * @var null|\Psr\Http\Message\ServerRequestInterface
     */
    protected $request;

    /**
     * @var string
     */
    private $content;

    /**
     * AbstractEncoder constructor.
     *
     * @param null|\Psr\Http\Message\ServerRequestInterface $request
     */
    public function __construct(?ServerRequestInterface $request = null)
    {
        $this->request = $request;
    }

    /**
     * Decode request content to array.
     *
     * @return mixed[]
     *
     * @throws \EoneoPay\Utils\Exceptions\BaseException
     * @throws \EoneoPay\ApiFormats\Exceptions\DecodeNullRequestException
     * @throws \RuntimeException
     */
    public function decode(): array
    {
        if ($this->request === null && $this->content === null) {
            throw new DecodeNullRequestException('Request must be set to decode content');
        }

        $content = $this->content ?? ($this->request !== null ? $this->request->getBody()->getContents() : '');

        if ($content === '') {
            return [];
        }

        return $this->decodeRequestContent($content);
    }

    /**
     * Create error response from given data, status code and headers.
     *
     * @param mixed $data
     * @param int|null $statusCode
     * @param string[]|null $headers
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function encodeError($data, ?int $statusCode = null, ?array $headers = null): ResponseInterface
    {
        return $this->encode($data, $statusCode, $headers);
    }

    /**
     * Manually set content to decode.
     *
     * @param string $content
     *
     * @return \EoneoPay\ApiFormats\Interfaces\EncoderInterface
     */
    public function setContent(string $content): EncoderInterface
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Decode request content to array.
     *
     * @param string $content
     *
     * @return mixed[]
     */
    abstract protected function decodeRequestContent(string $content): array;

    /**
     * Returns HTTP Content-Type header value.
     *
     * @return string
     */
    abstract protected function getContentTypeHeader(): string;

    /**
     * Get array representation of given data (recursively).
     *
     * @param mixed $data Data to convert to array
     * @param bool|null $isRoot Used to determine if return must be cast as array, cast only at root level
     *
     * @return mixed
     */
    protected function getDataAsArray($data, ?bool $isRoot = null)
    {
        $toResponseArray = [$data, 'toResponseArray'];
        // If this is serialisable or implements to response array, call it
        if (($data instanceof SerializableInterface) === true || \method_exists(...$toResponseArray) === true) {
            return \method_exists(...$toResponseArray) === true ? $toResponseArray() : $data->toArray();
        }

        // If given data is not iterable, cast result
        if (\is_iterable($data) === false) {
            // Cast only if root level to preserve arrays structure, like string[] or int[]
            return ($isRoot ?? true) ? (array)$data : $data;
        }

        return $this->processIterableObject($data);
    }

    /**
     * Get resource key for given data.
     *
     * @param mixed $data
     *
     * @return string
     *
     * @throws \ReflectionException
     */
    protected function getResourceKey($data): string
    {
        // If single item as object
        if (($data instanceof SerializableInterface) === true) {
            return $this->getResourceKeyForSerializable($data);
        }

        $data = (array)$data;
        if ($this->isCollection($data)) {
            foreach ($data as $item) {
                // Set resource key as first object plural name found
                if (($item instanceof SerializableInterface) === true) {
                    return $this->getResourceKeyForSerializable($item);
                }
            }
        }

        // Default key for single item as array and collection of arrays
        return 'items';
    }

    /**
     * Check if given data is collection.
     *
     * @param mixed[] $data
     *
     * @return bool
     */
    protected function isCollection(array $data): bool
    {
        // If key is not a integer, it means it's an associative array
        if (\is_int(\key($data)) === false) {
            return false;
        }

        $first = \reset($data);

        return \is_array($first)
            || ($first instanceof SerializableInterface) === true
            || \method_exists($first, 'toResponseArray');
    }

    /**
     * Instantiate response.
     *
     * @param string $content
     * @param int|null $statusCode
     * @param string[]|null $headers
     *
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    protected function response(string $content, ?int $statusCode = null, ?array $headers = null): ResponseInterface
    {
        $stream = new Stream('php://temp', 'rb+');
        $stream->write($content);
        $stream->seek(0);

        return new Response($stream, $statusCode ?? 200, \array_merge($headers ?? [], [
            'Content-Type' => $this->getContentTypeHeader()
        ]));
    }

    /**
     * Get resource key for serializable interface.
     *
     * @param \EoneoPay\Utils\Interfaces\SerializableInterface $serializable
     *
     * @return string
     *
     * @throws \ReflectionException
     *
     * @SuppressWarnings(PHPMD.StaticAccess) Third party Inflector::pluralize requires static access
     */
    private function getResourceKeyForSerializable(SerializableInterface $serializable): string
    {
        // If serializable defines resource key itself, return it
        if (\method_exists($serializable, 'getResourceKey')) {
            return (string)$serializable->getResourceKey();
        }

        // Guess resource key based on class name
        return Inflector::pluralize((new ReflectionClass($serializable))->getShortName());
    }

    /**
     * Recursively process iterable object
     *
     * @param mixed $data The data to process
     *
     * @return mixed[]
     */
    private function processIterableObject($data): array
    {
        // At this point we know it's an array
        $return = [];

        // We process it recursively
        if (\is_iterable($data)) {
            foreach ($data as $key => $value) {
                $return[$key] = $this->getDataAsArray($value, false);
            }
        }

        // Finally, return cast as array
        return $return;
    }
}
