<?php
declare(strict_types=1);

namespace Tests\EoneoPay\ApiFormats\TestCases;

use PHPUnit\Framework\TestCase;
use Tests\EoneoPay\ApiFormats\Stubs\CollectionInterfaceStub;
use Tests\EoneoPay\ApiFormats\Stubs\ObjectWithToResponseArray;
use Tests\EoneoPay\ApiFormats\Stubs\SerializableInterfaceStub;
use Tests\EoneoPay\ApiFormats\Stubs\SerializableInterfaceStubWithToResponseArray;
use Tests\EoneoPay\ApiFormats\Stubs\SerializableInterfaceWithGettersStub;
use Tests\EoneoPay\ApiFormats\Stubs\SerializableWithMagicCallStub;
use Tests\EoneoPay\ApiFormats\Stubs\TransformerAbstractAwareSerializableStub;
use Zend\Diactoros\ServerRequest;

abstract class RequestEncoderGuesserTestCase extends TestCase
{
    /**
     * Get test inputs for encoders tests.
     *
     * @return mixed[]
     */
    protected function getEncodersTests(): array
    {
        return [
            new CollectionInterfaceStub(),
            new SerializableInterfaceStub(), // Single item
            new SerializableInterfaceWithGettersStub(), // Single item with getters
            (new SerializableInterfaceStub())->toArray(), // Single item as array
            [], // Empty response
            [new SerializableInterfaceWithGettersStub(), (new SerializableInterfaceStub())->toArray()], // Collection,
            new SerializableInterfaceStubWithToResponseArray(), // toResponseArray method
            new \stdClass(),
            new TransformerAbstractAwareSerializableStub(),
            new SerializableWithMagicCallStub(),
            new ObjectWithToResponseArray()
        ];
    }

    /**
     * Get server request.
     *
     * @param string|null $accept
     * @param string|null $contentType
     *
     * @return \Zend\Diactoros\ServerRequest
     */
    protected function getRequest(?string $accept = null, ?string $contentType = null): ServerRequest
    {
        $headers = ['accept' => $accept ?? 'application/json'];

        if ($contentType !== null) {
            $headers['content-type'] = $contentType;
        }

        return new ServerRequest([], [], null, null, 'php://input', $headers);
    }
}
