<?php
declare(strict_types=1);

namespace EoneoPay\ApiFormats\External\Libraries\JsonApi;

use EoneoPay\ApiFormats\External\Interfaces\JsonApi\JsonApiConverterInterface;
use EoneoPay\ApiFormats\External\Interfaces\JsonApi\JsonApiHydratorInterface;
use WoohooLabs\Yang\JsonApi\Schema\Document;

class JsonApiConverter implements JsonApiConverterInterface
{
    /**
     * @var \EoneoPay\ApiFormats\External\Interfaces\JsonApi\JsonApiHydratorInterface
     */
    private $hydrator;

    /**
     * JsonApiConverter constructor.
     *
     * @param \EoneoPay\ApiFormats\External\Interfaces\JsonApi\JsonApiHydratorInterface|null $hydrator
     */
    public function __construct(?JsonApiHydratorInterface $hydrator = null)
    {
        $this->hydrator = $hydrator ?? new JsonApiHydrator();
    }

    /**
     * Convert json api string to array.
     *
     * @param string $jsonApi
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.StaticAccess) Third party Document::createFromArray requires static access
     */
    public function jsonApiToArray(string $jsonApi): array
    {
        return $this->hydrator->hydrate(Document::createFromArray(\json_decode($jsonApi, true) ?? []));
    }
}
