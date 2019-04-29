<?php
declare(strict_types=1);

namespace Tests\EoneoPay\ApiFormats\Stubs;

class ObjectWithToResponseArray
{
    /**
     * Get the contents of the repository as an array
     *
     * @return mixed[]
     */
    public function toResponseArray(): array
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
}
