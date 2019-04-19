<?php
declare(strict_types=1);

namespace Tests\EoneoPay\ApiFormats\Stubs;

final class SerializableWithMagicCallStub extends SerializableInterfaceStub
{
    /**
     * Stub implementation that should not be called.
     *
     * @param string $name
     * @param mixed[] $arguments
     *
     * @return void
     */
    public function __call(string $name, array $arguments): void
    {
        throw new \BadMethodCallException(\sprintf(
            'Call to undefined method %s::%s()',
            \get_class($this),
            $name
        ));
    }
}
