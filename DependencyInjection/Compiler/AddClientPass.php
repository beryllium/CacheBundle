<?php

namespace Beryllium\CacheBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Cache
 *
 * @package
 * @version $id$
 * @author Yaroslav Nechaev <mail@remper.ru>
 * @license See LICENSE.md
 */
class AddClientPass implements CompilerPassInterface {

    public function process(ContainerBuilder $container)
    {
        $def = $container->findDefinition('be_cache');
        $reference = new Reference('be_cache.client.' . $container->getParameter('be_cache.client'));
        $def->addMethodCall('setClient', array($reference));
    }
}