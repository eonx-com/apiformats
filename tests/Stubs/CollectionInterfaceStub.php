<?php
declare(strict_types=1);

namespace Tests\EoneoPay\ApiFormats\Stubs;

use EoneoPay\Utils\Interfaces\CollectionInterface;
use EoneoPay\Utils\Interfaces\SerializableInterface;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity) Collection is massive and requires all functionality
 * @SuppressWarnings(PHPMD.TooManyPublicMethods) Collection requires many public methods to work
 * @SuppressWarnings(PHPMD.UnusedFormalParameter) For testing purposes, some methods are empty
 */
final class CollectionInterfaceStub implements CollectionInterface
{
    /**
     * Convert collection to string
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toJson();
    }

    /**
     * Add an item to the collection
     *
     * @param mixed $item The item to add to the collection
     *
     * @return static
     */
    public function add($item)
    {
        return $this;
    }

    /**
     * Clear all items from a collection
     *
     * @return static
     */
    public function clear()
    {
        return $this;
    }

    /**
     * Collapse the collection of items into a single array
     *
     * @return static
     */
    public function collapse()
    {
        return $this;
    }

    /**
     * Run a filter over each of the items
     *
     * @param callable $callback A callback to process against the items
     *
     * @return static
     */
    public function filter(callable $callback)
    {
        return $this;
    }

    /**
     * Get the first item in the collection
     *
     * @return mixed The first item
     */
    public function first()
    {
        return 'first-item';
    }

    /**
     * Get item by key
     *
     * @param mixed $key The item to get
     * @param mixed $default The value to return if key isn't found
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return 'get-item';
    }

    /**
     * Get the items from the collection
     *
     * @return mixed[]
     */
    public function getItems(): array
    {
        return [];
    }

    /**
     * Determine if the collection has a specific key
     *
     * @param string $key The key to search for, can use dot notation
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        return true;
    }

    /**
     * Copy keys from one collection to this collection if keys exist in both
     *
     * @param \EoneoPay\Utils\Interfaces\SerializableInterface $source The source to check for the key in
     * @param mixed[] $keys The destination/source key pairs to process
     *
     * @return void
     */
    public function intersect(SerializableInterface $source, array $keys): void
    {
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     *
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     *
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Get the last item in the collection
     *
     * @return mixed
     */
    public function last()
    {
        return 'last-item';
    }

    /**
     * Run a map over each of the items
     *
     * @param callable $callback A callback to process against the items
     *
     * @return static
     */
    public function map(callable $callback)
    {
        return $this;
    }

    /**
     * Recursively merge an array into the collection
     *
     * @param mixed[] $data The data to merge into the collection
     *
     * @return void
     */
    public function merge(array $data): void
    {
    }

    /**
     * Get nth item from the items
     *
     * @param int $nth The item to get
     *
     * @return mixed
     */
    public function nth(int $nth)
    {
        return 'nth-item';
    }

    /**
     * Remove an item from a collection
     *
     * @param mixed $item The item to remove
     *
     * @return static
     */
    public function remove($item)
    {
        return $this;
    }

    /**
     * Recursively replace an array's values into the collection
     *
     * @param mixed[] $data The data to replace in the collection
     *
     * @return void
     */
    public function replace(array $data): void
    {
    }

    /**
     * Set a value to the collection
     *
     * @param string $key The key to set to the collection, can use dot notation
     * @param mixed $value The value to set for this key
     *
     * @return void
     */
    public function set(string $key, $value): void
    {
    }

    /**
     * Get the contents of the repository as an array
     *
     * @return mixed[]
     */
    public function toArray(): array
    {
        return [
            ['id' => 'id-1'],
            ['id' => 'id-2']
        ];
    }

    /**
     * Generate json from the repository
     *
     * @return string
     */
    public function toJson(): string
    {
        return \json_encode($this->jsonSerialize()) ?: '';
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
        return '<xml></xml>';
    }
}
