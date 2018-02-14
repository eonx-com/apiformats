<?php
declare(strict_types=1);

namespace Tests\EoneoPay\ApiFormats\Stubs;

use Tests\EoneoPay\ApiFormats\Stubs\Transformers\SerializableInterfaceTransformer;

class SerializableInterfaceWithGettersStub extends SerializableInterfaceStub
{
    /**
     * Return resource key for serialization.
     *
     * @return string
     */
    public function getResourceKey(): string
    {
        return 'my-resource-key';
    }

    /**
     * Return transformer class.
     *
     * @return string
     */
    public function getTransformer(): string
    {
        return SerializableInterfaceTransformer::class;
    }
}
