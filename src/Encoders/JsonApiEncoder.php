<?php
declare(strict_types=1);

namespace EoneoPay\ApiFormats\Encoders;

use EoneoPay\ApiFormats\External\Interfaces\JsonApi\JsonApiConverterInterface;
use EoneoPay\ApiFormats\External\Libraries\JsonApi\JsonApiConverter;
use EoneoPay\Utils\Interfaces\CollectionInterface;
use EoneoPay\Utils\Interfaces\SerializableInterface;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\NullResource;
use League\Fractal\Serializer\JsonApiSerializer;
use League\Fractal\TransformerAbstract;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) High coupling required due to fractal
 */
class JsonApiEncoder extends AbstractEncoder
{
    /**
     * @var \League\Fractal\Manager
     */
    private $fractal;

    /**
     * @var \EoneoPay\ApiFormats\External\Interfaces\JsonApi\JsonApiConverterInterface
     */
    private $jsonApiConverter;

    /**
     * JsonApiEncoder constructor.
     *
     * @param null|\Psr\Http\Message\ServerRequestInterface $request
     * @param \League\Fractal\Manager|null $fractal
     * @param \EoneoPay\ApiFormats\External\Interfaces\JsonApi\JsonApiConverterInterface|null $jsonApiConverter
     */
    public function __construct(
        ?ServerRequestInterface $request = null,
        ?Manager $fractal = null,
        ?JsonApiConverterInterface $jsonApiConverter = null
    ) {
        parent::__construct($request);

        $this->fractal = $fractal ?? new Manager();
        $this->fractal->setSerializer(new JsonApiSerializer());

        $this->jsonApiConverter = $jsonApiConverter ?? new JsonApiConverter();
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
     */
    public function encode($data, ?int $statusCode = null, ?array $headers = null): ResponseInterface
    {
        if ($this->isEmpty($data)) {
            return $this->emptyResponse($statusCode, $headers);
        }

        return $this->resourceResponse($data, $statusCode, $headers);
    }

    /**
     * @noinspection PhpMissingParentCallCommonInspection Parent intentionally bypassed
     *
     * {@inheritdoc}
     */
    public function encodeError($data, ?int $statusCode = null, ?array $headers = null): ResponseInterface
    {
        $errors = $this->getDataAsArray($data);

        if ($this->isCollection($data) === false) {
            $errors = [$errors];
        }

        return $this->response(\json_encode(['errors' => $errors]) ?: '', $statusCode, $headers);
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
        return $this->jsonApiConverter->jsonApiToArray($content);
    }

    /**
     * Returns HTTP Content-Type header value.
     *
     * @return string
     */
    protected function getContentTypeHeader(): string
    {
        return 'application/vnd.api+json';
    }

    /**
     * Return empty response.
     *
     * @param int|null $statusCode
     * @param string[]|null $headers
     *
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    private function emptyResponse(?int $statusCode = null, ?array $headers = null): ResponseInterface
    {
        return $this->response(
            \json_encode($this->fractal->createData(new NullResource())->toArray()) ?: '',
            $statusCode,
            $headers
        );
    }

    /**
     * Get resource class for given data.
     *
     * @param mixed $data
     *
     * @return string
     */
    private function getResourceClass($data): string
    {
        // Check if collection first since CollectionInterface extends SerializableInterface
        if (($data instanceof CollectionInterface) === true) {
            return Collection::class;
        }

        // If single item as object
        if (($data instanceof SerializableInterface) === true) {
            return Item::class;
        }

        return $this->isCollection((array)$data) ? Collection::class : Item::class;
    }

    /**
     * Return transformer for given data.
     *
     * @param mixed $data
     *
     * @return mixed
     */
    private function getTransformer($data)
    {
        // If data is an object and defines getTransformer method we use it
        $getTransformer = [$data, 'getTransformer'];
        if (($data instanceof SerializableInterface) === true &&
            \method_exists(...$getTransformer) &&
            \is_callable($getTransformer) === true
        ) {
            $transformer = $getTransformer();

            if (($transformer instanceof TransformerAbstract) === true) {
                return $transformer;
            }

            return new $transformer();
        }

        // Fallback to generic closure
        return function ($data): array {
            return $this->getDataAsArray($data);
        };
    }

    /**
     * Check is given data is empty.
     *
     * @param mixed $data
     *
     * @return bool
     */
    private function isEmpty($data): bool
    {
        return \count($this->getDataAsArray($data)) === 0;
    }

    /**
     * Return resource response.
     *
     * @param mixed $data
     * @param int|null $statusCode
     * @param string[]|null $headers
     *
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \ReflectionException
     */
    private function resourceResponse($data, ?int $statusCode = null, ?array $headers = null): ResponseInterface
    {
        $resourceClass = $this->getResourceClass($data);
        $resource = new $resourceClass($data, $this->getTransformer($data), $this->getResourceKey($data));

        return $this->response(
            \json_encode($this->fractal->createData($resource)->toArray()) ?: '',
            $statusCode,
            $headers
        );
    }
}
