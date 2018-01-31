<?php
declare(strict_types=1);

namespace Tests\EoneoPay\ApiFormats\Stubs;

use EoneoPay\ApiFormats\Bridge\Laravel\Traits\LaravelResponseTrait;
use Zend\Diactoros\Response;

class TraitMissingPsr7FactoryPropertyStub
{
    use LaravelResponseTrait;

    /**
     * Used to throw exception from unit tests.
     *
     * @return void
     */
    public function processTest(): void
    {
        $this->laravelResponse(new Response());
    }
}
