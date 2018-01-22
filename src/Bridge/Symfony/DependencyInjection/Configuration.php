<?php
declare(strict_types=1);

namespace EoneoPay\ApiFormats\Bridge\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
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
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     *
     * @throws \RuntimeException
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('eoneopay_api_formats');

        $rootNode
            ->children()
                ->arrayNode('supported')
                    ->defaultValue(static::$defaultFormats)
                    ->normalizeKeys(false)
                    ->useAttributeAsKey('format')
                    ->beforeNormalization()->ifArray()->then($this->getNormalizationClosure())->end()
                    ->prototype('array')
                        ->children()
                            ->arrayNode('mime_types')->prototype('scalar')->end()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

    /**
     * Normalize MIME types array.
     *
     * @return \Closure
     */
    private function getNormalizationClosure(): \Closure
    {
        return function ($array) {
            /**
             * @var array $array
             */
            foreach ($array as $format => $value) {
                if (isset($value['mime_types'])) {
                    continue;
                }

                $array[$format] = ['mime_types' => $value];
            }

            return $array;
        };
    }
}
