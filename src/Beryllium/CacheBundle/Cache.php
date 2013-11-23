<?php

namespace Beryllium\CacheBundle;

use Beryllium\CacheBundle\Client\CacheInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Cache
 *
 * @uses CacheInterface
 * @package
 * @version $id$
 * @author Kevin Boyd <beryllium@beryllium.ca>
 * @license See LICENSE.md
 */
class Cache implements CacheInterface
{
    /** @var ContainerInterface $container */
    protected $container;
    /** @var CacheInterface $client */
    protected $client;
    protected $debug = false;
    protected $ttl = 300;
    protected $safe = false;

    /**
     * Change the default lifetime of the data (default: 300 seconds - five minutes)
     *
     * @param int $ttl
     * @return void
     */
    public function setTtl($ttl)
    {
        $this->ttl = $ttl;
    }

    /**
     * Inject a cache client interface to interact with a custom cache service
     *
     * @param CacheInterface $client
     * @return void
     */
    public function setClient(CacheInterface $client)
    {
        $this->client = $client;
        $this->safe = true;
    }

    /**
     * Setting debug preference
     *
     * @param boolean $debug
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;
    }

    /**
     * Retrieve a value from the cache using the provided key
     *
     * @param string|array $key The unique key or array of keys identifying the data to be retrieved.
     * @return mixed The requested data, or false if there is an error
     */
    public function get($key)
    {
        if (!$this->safe) {
            return false;
        }

        return $this->client->get($key);
    }

    /**
     * Add a key/value to the cache
     *
     * @param string $key A unique key to identify the data you want to store
     * @param mixed $value The value you want to store in the cache
     * @param int $ttl Optional: Lifetime of the data
     * @return boolean
     */
    public function set($key, $value, $ttl = null)
    {
        if (!$this->safe) {
            return false;
        }

        $ttl = is_null($ttl) ? $this->ttl : $ttl;

        return $this->client->set($key, $value, $ttl);
    }

    /**
     * Delete a key from the cache
     *
     * @param string $key Unique key
     * @return boolean
     */
    public function delete($key)
    {
        if (!$this->safe) {
            return false;
        }

        return $this->client->delete($key);
    }

    /**
     * @return CacheInterface|null
     */
    public function getClient()
    {
        if (!$this->safe) {
            return null;
        }

        return $this->client;
    }
}
