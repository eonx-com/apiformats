<?php
declare(strict_types=1);

namespace Tests\EoneoPay\ApiFormats\Bridge\Symfony\DependencyInjection;

use Tests\EoneoPay\ApiFormats\TestCases\DependencyInjectionTestCase;

class ConfigurationTest extends DependencyInjectionTestCase
{
    /**
     * Configuration should be the same as the one passed.
     */
    public function testCustomConfigurationWithMimeTypes(): void
    {
        $configs = [
            'eoneopay_api_formats' => [
                'supported' => [
                    'custom_json' => ['mime_types' => ['application/json']],
                    'custom_xml' => ['mime_types' => ['text/xml']],
                    'custom_csv' => ['mime_types' => ['text/csv']]
                ]
            ]
        ];

        $config = $this->processConfiguration($configs);

        self::assertArrayHasKey('supported', $config);
        self::assertEquals($configs['eoneopay_api_formats']['supported'], $config['supported']);
    }

    /**
     * Configuration should happen mime_types key if not set.
     */
    public function testCustomConfigurationWithoutMimeTypes(): void
    {
        $configs = [
            'eoneopay_api_formats' => [
                'supported' => [
                    'custom_json' => ['application/json'],
                    'custom_xml' => ['text/xml'],
                    'custom_csv' => ['text/csv']
                ]
            ]
        ];

        $expected = [
            'custom_json' => ['mime_types' => ['application/json']],
            'custom_xml' => ['mime_types' => ['text/xml']],
            'custom_csv' => ['mime_types' => ['text/csv']]
        ];

        $config = $this->processConfiguration($configs);

        self::assertArrayHasKey('supported', $config);
        self::assertEquals($expected, $config['supported']);
    }

    /**
     * Configuration should fallback to default formats when not set
     */
    public function testFallbackToDefaultConfiguration(): void
    {
        $config = $this->processConfiguration([]);

        self::assertArrayHasKey('supported', $config);
        self::assertEquals(static::$defaultFormats, $config['supported']);
    }
}
