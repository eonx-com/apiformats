<?php
declare(strict_types=1);

namespace Tests\EoneoPay\ApiFormats\TestCases;

use EoneoPay\ApiFormats\Bridge\Symfony\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

class DependencyInjectionTestCase extends TestCase
{
    /**
     * Default supported formats.
     *
     * @var array
     */
    protected static $defaultFormats = [
        'json' => ['mime_types' => ['application/json']],
        'xml' => ['mime_types' => ['application/xml', 'text/xml']]
    ];

    /**
     * Process configuration and return configs array.
     *
     * @param array $configs
     *
     * @return array
     */
    protected function processConfiguration(array $configs): array
    {
        return (new Processor())->processConfiguration(new Configuration(), $configs);
    }
}
