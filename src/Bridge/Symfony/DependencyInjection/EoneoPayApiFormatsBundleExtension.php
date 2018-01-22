<?php
declare(strict_types=1);

namespace EoneoPay\ApiFormats\Bridge\Symfony\DependencyInjection;

use EoneoPay\ApiFormats\Helpers\FormatsHelper;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class EoneoPayApiFormatsBundleExtension extends Extension
{
    /**
     * Loads a specific configuration.
     *
     * @param array $configs
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     * @throws \InvalidArgumentException When provided tag is not defined in this extension
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configs = $this->processConfiguration(new Configuration(), $configs);

        $container->setParameter(
            'eoneopay_api_formats.supported',
            (new FormatsHelper())->normalizeFormats($configs['supported'])
        );

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');
    }
}
