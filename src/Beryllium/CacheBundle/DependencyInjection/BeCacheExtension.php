<?php

namespace Beryllium\CacheBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class BeCacheExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter('be_cache.client', $config['client']);
        $container->setParameter('be_cache.ttl', $config['ttl']);
        $container->setParameter('be_cache.debug', $config['debug']);
        $container->setParameter('be_cache.memcache.ip', $config['parameters']['memcache']['ip']);
        $container->setParameter('be_cache.memcache.port', $config['parameters']['memcache']['port']);
        $container->setParameter('be_cache.filecache.path', $config['parameters']['filecache']['path']);
    }
}