<?php

namespace Beryllium\CacheBundle;

use Beryllium\CacheBundle\DependencyInjection\Compiler\AddClientPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * BerylliumCacheBundle 
 * 
 * @uses Bundle
 * @package 
 * @version 0.1
 * @author Kevin Boyd <beryllium@beryllium.ca> 
 * @license See LICENSE.md
 */
class BeCacheBundle extends Bundle
{
    public function build(ContainerBuilder $builder)
    {
        parent::build($builder);

        $builder->addCompilerPass(new AddClientPass());
    }
}
