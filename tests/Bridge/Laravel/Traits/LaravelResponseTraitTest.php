<?php
declare(strict_types=1);

namespace Tests\EoneoPay\ApiFormats\Bridge\Laravel\Traits;

use EoneoPay\ApiFormats\Bridge\Laravel\Exceptions\InvalidPsr7FactoryException;
use PHPUnit\Framework\TestCase;
use Tests\EoneoPay\ApiFormats\Stubs\TraitMissingPsr7FactoryPropertyStub;

class LaravelResponseTraitTest extends TestCase
{
    /**
     * Trait should throw exception if class does not define PSR-7 factory property.
     *
     * @return void
     *
     * @throws \EoneoPay\ApiFormats\Bridge\Laravel\Exceptions\InvalidPsr7FactoryException
     */
    public function testInvalidPsr7FactoryException(): void
    {
        $this->expectException(InvalidPsr7FactoryException::class);

        (new TraitMissingPsr7FactoryPropertyStub())->processTest();
    }
}
