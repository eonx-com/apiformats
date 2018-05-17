<?php
declare(strict_types=1);

namespace EoneoPay\ApiFormats\External\Interfaces\JsonApi;

interface JsonApiConverterInterface
{
    /**
     * Convert json api string to array.
     *
     * @param string $jsonApi
     *
     * @return mixed[]
     */
    public function jsonApiToArray(string $jsonApi): array;
}
