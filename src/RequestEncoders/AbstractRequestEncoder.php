<?php
declare(strict_types=1);

namespace EoneoPay\ApiFormats\RequestEncoders;

use Doctrine\Common\Inflector\Inflector;
use EoneoPay\ApiFormats\Exceptions\DecodeNullRequestException;
use EoneoPay\ApiFormats\Interfaces\RequestEncoderInterface;
use EoneoPay\Utils\Interfaces\SerializableInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Stream;

abstract class AbstractRequestEncoder implements RequestEncoderInterface
{
    /**
     * @var string
     */
    private $content;

    /**
     * @var null|ServerRequestInterface
     */
    protected $request;

    /**
     * AbstractRequestEncoder constructor.
     *
     * @param null|ServerRequestInterface $request
     */
    public function __construct(?ServerRequestInterface $request = null)
    {
        $this->request = $request;
    }

    /**
     * Decode request content to array.
     *
     * @return array
     *
     * @throws \EoneoPay\Utils\Exceptions\BaseException
     * @throws \EoneoPay\ApiFormats\Exceptions\DecodeNullRequestException
     * @throws \RuntimeException
     */
    public function decode(): array
    {
        if (null === $this->request && null === $this->content) {
            throw new DecodeNullRequestException('Request must be set to decode content');
        }

        $content = $this->content ?? $this->request->getBody()->getContents();

        if ('' === $content) {
            return [];
        }

        return $this->decodeRequestContent($content);
    }

    /**
     * Manually set content to decode.
     *
     * @param string $content
     *
     * @return \EoneoPay\ApiFormats\Interfaces\RequestEncoderInterface
     */
    public function setContent(string $content): RequestEncoderInterface
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Decode request content to array.
     *
     * @param string $content
     *
     * @return array
     */
    abstract protected function decodeRequestContent(string $content): array;

    /**
     * Returns HTTP Content-Type header value.
     *
     * @return string
     */
    abstract protected function getContentTypeHeader(): string;

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
        if ($data instanceof SerializableInterface) {
            return $this->getResourceKeyForSerializable($data);
        }

        $data = (array) $data;
        if ($this->isCollection($data)) {
            foreach ($data as $item) {
                // Set resource key as first object plural name found
                if ($item instanceof SerializableInterface) {
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
     * @param array $data
     *
     * @return bool
     */
    protected function isCollection(array $data): bool
    {
        return \is_int(\key($data)) && (\is_array(\reset($data)) || \reset($data) instanceof SerializableInterface);
    }

    /**
     * Instantiate response.
     *
     * @param string $content
     * @param int|null $statusCode
     * @param array|null $headers
     *
     * @return ResponseInterface
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
            return $serializable->getResourceKey();
        }

        // Guess resource key based on class name
        return Inflector::pluralize((new \ReflectionClass($serializable))->getShortName());
    }
}
