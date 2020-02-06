<?php
declare(strict_types=1);

namespace Tests\EoneoPay\ApiFormats\Stubs;

use EoneoPay\ApiFormats\Bridge\Laravel\Traits\LaravelResponseTrait;
use Laminas\Diactoros\Response;

class TraitMissingPsr7FactoryPropertyStub
{
    use LaravelResponseTrait;

    /**
     * Used to throw exception from unit tests.
     *
     * @return void
     *
     * @throws \EoneoPay\ApiFormats\Bridge\Laravel\Exceptions\InvalidPsr7FactoryException
     */
    public function processTest(): void
    {
        $this->createLaravelResponseFromPsr(new Response());
    }
}
