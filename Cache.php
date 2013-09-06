<?php

namespace Beryllium\CacheBundle;

use Beryllium\CacheBundle\CacheInterface;
use Beryllium\CacheBundle\CacheClientInterface;

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
    public $dic = false;

    protected $client = null;
    protected $safe = false;
    protected $ttl = 300;

    /**
     * Prep the cache
     *
     * @param CacheClientInterface $client Optional cache object/service
     * @access public
     * @return void
     */
    public function __construct(CacheClientInterface $client = null)
    {
        if (!empty($client)) {
            if (is_object($client) && ($client instanceof CacheClientInterface)) {
                $this->client = $client;
            }
            else {
                throw new \Exception('Invalid Cache Client Interface');
            }
        }
    }

    /**
     * Inject a dependency injection container (optional)
     *
     * @param mixed $dic The container
     * @access public
     * @return void
     */
    public function setContainer($dic)
    {
        $this->dic = $dic;
    }

    /**
     * Change the default lifetime of the data (default: 300 seconds - five minutes)
     *
     * @param int $ttl
     * @access public
     * @return void
     */
    public function setTtl($ttl)
    {
        $this->ttl = $ttl;
    }

    /**
     * Inject a cache client interface to interact with a custom cache service
     *
     * @param CacheClientInterface $client The client object or service
     * @access public
     * @return void
     */
    public function setClient(CacheClientInterface $client)
    {
        if (is_object($client) && ($client instanceof CacheClientInterface))
            $this->client = $client;
        else {
            throw new \Exception('Invalid Cache Client Interface');
        }
    }

    /**
     * Retrieve a value from the cache using the provided key
     *
     * @param string|array $key The unique key or array of keys identifying the data to be retrieved.
     * @access public
     * @return mixed The requested data, or false if there is an error
     */
    public function get($key)
    {
        if ($this->isSafe() && !empty($key)) {
            return $this->client->get($key);
        }

        return false;
    }

    /**
     * Add a key/value to the cache
     *
     * @param string $key A unique key to identify the data you want to store
     * @param string $value The value you want to store in the cache
     * @param int $ttl Optional: Lifetime of the data
     * @access public
     * @return mixed Whatever the CacheClientObject returns, or false.
     */
    public function set($key, $value, $ttl = null)
    {
        $ttl = (null !== $ttl) ? $ttl : $this->ttl;

        if ($this->isSafe() && !empty($key)) {
            return $this->client->set($key, $value, $ttl);
        }

        return false;
    }

    /**
     * Delete a key from the cache
     *
     * @param string $key Unique key
     * @access public
     * @return void
     */
    public function delete($key)
    {
        if ($this->isSafe() && !empty($key)) {
            return $this->client->delete($key);
        }

        return false;
    }

    /**
     * Checks if the cache is in a usable state
     *
     * @access public
     * @return boolean True if the cache is usable, otherwise false
     */
    public function isSafe()
    {
        if ($this->client instanceof CacheClientInterface) {
            return $this->client->isSafe();
        }

        return $this->safe;
    }
}
