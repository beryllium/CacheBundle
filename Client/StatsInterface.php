<?php

namespace Beryllium\CacheBundle\Client;

use Beryllium\CacheBundle\Statistics;

/**
 * Interface for generating statistics
 *
 * @package
 * @version $id$
 * @author Yaroslav Nechaev <mail@remper.ru>
 * @license See LICENSE.md
 */
interface StatsInterface {
    /**
     * @return Statistics[]
     */
    public function getStats();
} 