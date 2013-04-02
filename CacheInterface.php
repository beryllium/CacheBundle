<?php

namespace Beryllium\CacheBundle;

use Beryllium\CacheBundle\CacheClientInterface;

/**
 * CacheInterface 
 * 
 * @package 
 * @version $id$
 * @author Kevin Boyd <beryllium@beryllium.ca> 
 * @license See LICENSE.md
 */
interface CacheInterface
{
    /**
     * @param CacheClientInterface $client
     */
    public function __construct(CacheClientInterface $client = null);

    /**
     * @param string $key
     * @return mixed
     */
    public function get($key);

    /**
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     * @return mixed
     */
    public function set($key, $value, $ttl);

    /**
     * @param string $key
     * @return mixed
     */
    public function delete($key);

    /**
     * @return bool
     */
    public function isSafe();
}
