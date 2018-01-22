<?php
declare(strict_types=1);

namespace Tests\EoneoPay\ApiFormats\Helpers;

use EoneoPay\ApiFormats\Helpers\FormatsHelper;
use PHPUnit\Framework\TestCase;

class FormatsHelperTest extends TestCase
{
    /**
     * Default supported formats.
     *
     * @var array
     */
    private static $defaultFormats = [
        'json' => ['mime_types' => ['application/json']],
        'xml' => ['mime_types' => ['application/xml', 'text/xml']]
    ];

    /**
     * Normalized formats.
     *
     * @var array
     */
    private static $normalizedFormats = [
        'json' => ['application/json'],
        'xml' => ['application/xml', 'text/xml']
    ];

    /**
     * Formats helper should normalized properly given formats.
     */
    public function testNormalizeFormats(): void
    {
        self::assertEquals(static::$normalizedFormats, (new FormatsHelper())->normalizeFormats(static::$defaultFormats));
    }
}
