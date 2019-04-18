<?php
declare(strict_types=1);

namespace Tests\EoneoPay\ApiFormats\Stubs;

class SerializableInterfaceStubWithToResponseArray extends SerializableInterfaceStub
{
    /**
     * Method to override parent toArray method.
     *
     * @return mixed[]
     */
    public function toResponseArray(): array
    {
        return $this->toArray();
    }
}
