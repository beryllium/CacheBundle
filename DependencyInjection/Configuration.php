<?php

namespace Beryllium\CacheBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $tb = new TreeBuilder();

        return $tb
            ->root('be_cache')
                ->children()
                    ->scalarNode('client')->defaultValue('filecache')->end()
                    ->scalarNode('ttl')->defaultValue(300)->end()
                    ->arrayNode('parameters')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->arrayNode('memcache')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('ip')->defaultValue('127.0.0.1')->end()
                                    ->scalarNode('port')->defaultValue(11211)->end()
                                ->end()
                            ->end()
                            ->arrayNode('filecache')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('path')->defaultValue("%kernel.cache_dir%/apc")->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ->booleanNode('debug')->defaultValue('%kernel.debug%')->end()
                ->end()
            ->end();
    }
}