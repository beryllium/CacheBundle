<?php

namespace Beryllium\CacheBundle\Client;

use Beryllium\CacheBundle\Statistics;

/**
 * APC Client
 *
 * @package
 * @version $id$
 * @author Yaroslav Nechaev <mail@remper.ru>
 * @license See LICENSE.md
 */
class APCClient implements CacheInterface, StatsInterface
{
    private $safe;

    public function __construct()
    {
        if (!extension_loaded('apc')) {
            $this->safe = false;

            return;
        }

        $this->safe = true;
    }

    /**
     * Retrieve the value corresponding to a provided key
     *
     * @param string $key Unique identifier
     * @return mixed Result from the cache
     */
    public function get($key)
    {
        if (!$this->safe) {
            return false;
        }

        return apc_fetch($key);
    }

    /**
     * Add a value to the cache under a unique key
     *
     * @param string $key Unique key to identify the data
     * @param mixed $value Data to store in the cache
     * @param int $ttl Lifetime for stored data (in seconds)
     * @return boolean
     */
    public function set($key, $value, $ttl)
    {
        if (!$this->safe) {
            return false;
        }

        return apc_store($key, $value, $ttl);
    }

    /**
     * Delete a value from the cache
     *
     * @param string $key
     * @return boolean
     */
    public function delete($key)
    {
        if (!$this->safe) {
            return false;
        }

        return apc_delete($key);
    }


    /**
     * @return Statistics[]
     */
    public function getStats()
    {
        if (!$this->safe) {
            return array();
        }

        $apc_info = apc_cache_info('user', true);
        return array('APC' => new Statistics($apc_info['num_hits'], $apc_info['num_misses']));
    }
}