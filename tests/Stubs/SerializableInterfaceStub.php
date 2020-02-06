<?php
declare(strict_types=1);

namespace Tests\EoneoPay\ApiFormats\Stubs;

use EoneoPay\Utils\Interfaces\SerializableInterface;

class SerializableInterfaceStub implements SerializableInterface
{
    /**
     * Specify data which should be serialized to JSON
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     *
     * @return mixed[] data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     *
     * @since 5.4.0
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Get the contents of the repository as an array
     *
     * @return mixed[]
     */
    public function toArray(): array
    {
        return [
            'id' => 'my-id',
            'email' => 'email@eoneopay.com.au',
            'child' => [
                'id' => 'child-id',
                'email' => 'child-email@eoneopay.com.au'
            ]
        ];
    }

    /**
     * Generate json from the repository
     *
     * @return string
     */
    public function toJson(): string
    {
        $json = \json_encode($this->jsonSerialize());

        return $json === false ? '' : $json;
    }

    /**
     * Generate XML string from the repository
     *
     * @param string|null $rootNode The name of the root node
     *
     * @return string|null
     */
    public function toXml(?string $rootNode = null): ?string
    {
        $rootNode = $rootNode ?? 'data';

        return \sprintf('<%s><email>email@eoneopay.com.au</email></%s>', $rootNode, $rootNode);
    }
}
