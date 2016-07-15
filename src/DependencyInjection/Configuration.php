<?php

namespace TimeInc\SwaggerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('swagger');

        $rootNode->children()

                ->scalarNode('version')->defaultValue('2.0')->end()
                ->scalarNode('host')->end()
                ->scalarNode('base_path')->end()
                ->arrayNode('info')
                    ->children()
                        ->scalarNode('title')->isRequired()->end()
                        ->scalarNode('version')->isRequired()->end()
                        ->scalarNode('description')->end()
                    ->end()
                ->end()

                ->arrayNode('schemes')
                    ->prototype('scalar')->end()
                ->end()

                ->arrayNode('consumes')
                    ->prototype('scalar')->end()
                ->end()

                ->arrayNode('produces')
                    ->prototype('scalar')->end()
                ->end()

                ->arrayNode('annotations')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('bundles')
                            ->prototype('scalar')->end()
                        ->end()

                        ->arrayNode('paths')
                            ->prototype('scalar')->end()
                        ->end()

                        ->arrayNode('paths_exclude')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('api_gateway')
                    ->canBeEnabled()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
