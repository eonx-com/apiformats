<?php
declare(strict_types=1);

namespace Tests\EoneoPay\ApiFormats\Stubs;

use ArrayIterator;
use EoneoPay\Utils\Interfaces\CollectionInterface;
use EoneoPay\Utils\Interfaces\SerializableInterface;
use Iterator;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity) Collection is massive and requires all functionality
 * @SuppressWarnings(PHPMD.TooManyPublicMethods) Collection requires many public methods to work
 * @SuppressWarnings(PHPMD.UnusedFormalParameter) For testing purposes, some methods are empty
 */
final class CollectionInterfaceStub implements CollectionInterface
{
    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return $this->toJson();
    }

    /**
     * {@inheritdoc}
     */
    public function add($item)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function collapse()
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function filter(callable $callback)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function first()
    {
        return 'first-item';
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        return 'get-item';
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->getItems());
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $key): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function intersect(SerializableInterface $source, array $keys): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function last()
    {
        return 'last-item';
    }

    /**
     * {@inheritdoc}
     */
    public function map(callable $callback)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function merge(array $data): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function nth(int $nth)
    {
        return 'nth-item';
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $offset
     */
    public function offsetExists($offset): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $offset
     */
    public function offsetUnset($offset): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function remove($item)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function replace(array $data): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $key, $value): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return [
            ['id' => 'id-1'],
            ['id' => 'id-2']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function toJson(): string
    {
        $json = \json_encode($this->jsonSerialize());

        return $json === false ? '' : $json;
    }

    /**
     * {@inheritdoc}
     */
    public function toXml(?string $rootNode = null): ?string
    {
        return '<xml></xml>';
    }
}
