<?php
declare(strict_types=1);

namespace Tests\EoneoPay\ApiFormats\Stubs;

use Tests\EoneoPay\ApiFormats\Stubs\Transformers\SerializableInterfaceTransformer;

class TransformerAbstractAwareSerializableStub extends SerializableInterfaceStub
{
    /**
     * Return instance of TransformerAbstract.
     *
     * @return \Tests\EoneoPay\ApiFormats\Stubs\Transformers\SerializableInterfaceTransformer
     */
    public function getTransformer(): SerializableInterfaceTransformer
    {
        return new SerializableInterfaceTransformer();
    }
}
