<?php
declare(strict_types=1);

namespace EoneoPay\ApiFormats\External\Libraries\JsonApi;

use EoneoPay\ApiFormats\External\Interfaces\JsonApi\JsonApiHydratorInterface;
use WoohooLabs\Yang\JsonApi\Schema\Document;
use WoohooLabs\Yang\JsonApi\Schema\ResourceObject;

class JsonApiHydrator implements JsonApiHydratorInterface
{
    /**
     * Return array representation of document.
     *
     * @param \WoohooLabs\Yang\JsonApi\Schema\Document $document
     *
     * @return array
     */
    public function hydrate(Document $document): array
    {
        if (false === $document->hasAnyPrimaryResources()) {
            return [];
        }

        if ($document->isSingleResourceDocument() && null !== $document->primaryResource()) {
            return [$document->primaryResource()->type() => $this->hydratePrimaryResource($document)];
        }

        return $this->hydratePrimaryResources($document);
    }

    /**
     * Get object from map.
     *
     * @param string $type
     * @param string $objectId
     * @param array $resourceMap
     *
     * @return array|null
     */
    private function getObjectFromMap(string $type, string $objectId, array $resourceMap): ?array
    {
        return $resourceMap[$type . '-' . $objectId] ?? null;
    }

    /**
     * Hydrate primary resource.
     *
     * @param \WoohooLabs\Yang\JsonApi\Schema\Document $document
     *
     * @return array
     */
    private function hydratePrimaryResource(Document $document): array
    {
        $resourceMap = [];

        return $this->hydrateResource($document->primaryResource(), $document, $resourceMap);
    }

    /**
     * Hydrate primary resources.
     *
     * @param \WoohooLabs\Yang\JsonApi\Schema\Document $document
     *
     * @return array
     */
    private function hydratePrimaryResources(Document $document): array
    {
        $result = [];
        $resourceMap = [];
        $type = null;

        foreach ($document->primaryResources() as $primaryResource) {
            if (null === $type) {
                $type = $primaryResource->type();
            }

            $result[] = $this->hydrateResource($primaryResource, $document, $resourceMap);
        }

        return [$type => $result];
    }

    /**
     * Hydrate resource.
     *
     * @param null|\WoohooLabs\Yang\JsonApi\Schema\ResourceObject $resource
     * @param \WoohooLabs\Yang\JsonApi\Schema\Document $document
     * @param array $resourceMap
     *
     * @return array
     */
    private function hydrateResource(?ResourceObject $resource = null, Document $document, array &$resourceMap): array
    {
        // This is only here for type safety, null is checked before calling method from hydrate()
        if (null === $resource) {
            // @codeCoverageIgnoreStart
            return [];
            // @codeCoverageIgnoreEnd
        }

        // Fill basic attributes of the resource
        $result = $this->hydrateResultArray($resource);

        // Save resource to the identity map
        $this->saveObjectToMap($result, $resourceMap);

        // Fill relationships
        foreach ($resource->relationships() as $name => $relationship) {
            foreach ($relationship->resourceLinks() as $link) {
                $object = $this->getObjectFromMap($link['type'], $link['id'], $resourceMap);

                if (null === $object && $document->hasIncludedResource($link['type'], $link['id'])) {
                    $relatedResource = $document->resource($link['type'], $link['id']);

                    if (null !== $relatedResource) {
                        $object = $this->hydrateResource($relatedResource, $document, $resourceMap);
                    }
                }

                if (null === $object) {
                    continue;
                }

                if ($relationship->isToOneRelationship()) {
                    $result[$name] = $object;

                    continue;
                }

                $result[$name][] = $object;
            }
        }

        return $result;
    }

    /**
     * Initiate and hydrate result array form given resource.
     *
     * @param \WoohooLabs\Yang\JsonApi\Schema\ResourceObject $resource
     *
     * @return array
     */
    private function hydrateResultArray(ResourceObject $resource): array
    {
        $result = [
            'id' => $resource->id(),
            'type' => $resource->type()
        ];

        foreach ($resource->attributes() as $attribute => $value) {
            $result[$attribute] = $value;
        }

        return $result;
    }

    /**
     * Save object to map.
     *
     * @param array $object
     * @param array $resourceMap
     *
     * @return void
     */
    private function saveObjectToMap(array $object, array &$resourceMap): void
    {
        $resourceMap[$object['type'] . '-' . $object['id']] = $object;
    }
}
