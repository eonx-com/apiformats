<?php
declare(strict_types=1);

namespace EoneoPay\ApiFormats\External\Interfaces\JsonApi;

use WoohooLabs\Yang\JsonApi\Schema\Document;

interface JsonApiHydratorInterface
{
    /**
     * Return array representation of document.
     *
     * @param \WoohooLabs\Yang\JsonApi\Schema\Document $document
     *
     * @return mixed[]
     */
    public function hydrate(Document $document): array;
}
