<?php
declare(strict_types=1);

namespace Tests\EoneoPay\ApiFormats\Bridge\Symfony\DependencyInjection;

use EoneoPay\ApiFormats\Bridge\Symfony\DependencyInjection\EoneoPayApiFormatsBundleExtension;
use EoneoPay\ApiFormats\EventListeners\SetFormatOnRequestListener;
use EoneoPay\ApiFormats\Helpers\FormatsHelper;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tests\EoneoPay\ApiFormats\TestCases\DependencyInjectionTestCase;

class EoneoPayApiFormatsBundleExtensionTest extends DependencyInjectionTestCase
{
    /**
     * Extension should register supported formats parameter and format listener into container
     */
    public function testDefinitionsWithDefaultConfiguration(): void
    {
        $container = new ContainerBuilder();
        (new EoneoPayApiFormatsBundleExtension())->load([], $container);

        self::assertEquals(
            (new FormatsHelper())->normalizeFormats(static::$defaultFormats),
            $container->getParameter('eoneopay_api_formats.supported')
        );
        self::assertInstanceOf(SetFormatOnRequestListener::class, $container->get('eoneopay_api.set_format_listener'));
    }
}
