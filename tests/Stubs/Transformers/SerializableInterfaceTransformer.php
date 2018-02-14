<?php
declare(strict_types=1);

namespace Tests\EoneoPay\ApiFormats\Stubs\Transformers;

use EoneoPay\Utils\Interfaces\SerializableInterface;
use League\Fractal\TransformerAbstract;

class SerializableInterfaceTransformer extends TransformerAbstract
{
    /**
     * Get serializable array representation.
     *
     * @param \EoneoPay\Utils\Interfaces\SerializableInterface $serializable
     *
     * @return array
     */
    public function transform(SerializableInterface $serializable): array
    {
        return $serializable->toArray();
    }
}
