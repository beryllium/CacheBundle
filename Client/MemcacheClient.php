<?php

namespace Beryllium\CacheBundle\Client;

use Beryllium\CacheBundle\CacheClientInterface;

/**
 * Client interface for Memcached servers
 *
 * @uses CacheClientInterface
 * @package
 * @version $id$
 * @author Kevin Boyd <beryllium@beryllium.ca>
 * @license See LICENSE.md
 */
class MemcacheClient implements CacheClientInterface
{
    const PREFIX_MAX_LENGTH = 128;

    protected $safe = false;
    protected $mem = null;
    protected $servers = array();
    protected $sockttl = 0.2;

    /**
     * Constructs the cache client using an injected Memcache instance
     *
     * @access public
     */
    public function __construct(\Memcached $memcached)
    {
        $this->mem = $memcached;
    }

    /**
     * Add a server to the memcached pool.
     *
     * Does not probe server, does not set Safe to true.
     *
     * Should really be private, or modified to handle the probeServer action itself.
     *
     * @param string $ip Location of memcached server
     * @param int $port Optional: Port number (default: 11211)
     * @access public
     * @return void
     */
    public function addServer($ip, $port = 11211)
    {
        if (is_object($this->mem)) {
            return $this->mem->addServer($ip, $port);
        }
    }

    /**
     * Add an array of servers to the memcached pool
     *
     * Uses ProbeServer to verify that the connection is valid.
     *
     * Format of array:
     *
     *   $servers[ '127.0.0.1' ] = 11211;
     *
     * Logic is somewhat flawed, of course, because it wouldn't let you add multiple
     * servers on the same IP.
     *
     * Serious flaw, right? ;-)
     *
     * @param array $servers See above format definition
     * @access public
     * @return void
     */
    public function addServers(array $servers)
    {
        if (count($servers) == 0) {
            return false;
        }

        foreach ($servers as $ip => $port) {
            if (intval($port) == 0) {
                $port = null;
            }

            if ($this->probeServer($ip, $port)) {
                $status = $this->addServer($ip, $port);
                $this->safe = true;
            }
        }
    }

    /**
     * Spend a few tenths of a second opening a socket to the requested IP and port
     *
     * The purpose of this is to verify that the server exists before trying to add it,
     * to cut down on weird errors when doing ->get(). This could be a controversial or
     * flawed way to go about this.
     *
     * @param string $ip IP address (or hostname, possibly)
     * @param int $port Port that memcached is running on
     * @access public
     * @return boolean True if the socket opens successfully, or false if it fails
     */
    public function probeServer($ip, $port)
    {
        $errno = null;
        $errstr = null;
        $fp = @fsockopen($ip, $port, $errno, $errstr, $this->sockttl);

        if ($fp) {
            fclose($fp);

            return true;
        } else {
            return false;
        }
    }

    /**
     * Retrieve a value from memcached
     *
     * @param string $key Unique identifier
     * @access public
     * @return mixed Requested value, or false if an error occurs
     */
    public function get($key)
    {
        if ($this->isSafe()) {

            return $this->mem->get($key);
        }

        return false;
    }

    /**
     * Retrieve a set of values from memcached
     *
     * @param array $keys of Unique identifiers
     * @access public
     * @return mixed Requested value, or false if an error occurs
     */
    public function getMulti($keys)
    {
        if ($this->isSafe()) {

            return $this->mem->getMulti($keys);
        }

        return false;
    }

    /**
     * Add a value to the memcached
     *
     * @param string $key Unique key
     * @param mixed $value A value. I recommend a string, be it serialized or not - other values haven't been tested :)
     * @param int $ttl Number of seconds for the value to be valid for
     * @access public
     * @return void
     */
    public function set($key, $value, $ttl)
    {
        if ($this->isSafe()) {

            return $this->mem->set($key, $value, $ttl);
        }

        return false;
    }

    /**
     * Add a set of values to the memcached
     *
     * @param array $values An array of pairs key-value.
     * @param int $ttl Number of seconds for the value to be valid for
     * @access public
     * @return void
     */
    public function setMulti($values, $ttl)
    {
        if ($this->isSafe()) {
            return $this->mem->setMulti($values, $ttl);
        }

        return false;
    }

    /**
     * Delete a value from the memcached
     *
     * @param string $key Unique key
     * @access public
     * @return void
     */
    public function delete($key)
    {
        if ($this->isSafe()) {

            return $this->mem->delete($key, 0);
        }

        return false;
    }

    /**
     * Delete a set of values from the memcached
     *
     * @param array $keys Unique key
     * @param int $time Time to wait before delete
     * @access public
     * @return void
     */
    public function deleteMulti($keys, $time = 0)
    {
        if ($this->isSafe()) {

            return $this->mem->deleteMulti($keys, $time);
        }

        return false;
    }

    /**
     * Delete a set of values from the memcached
     *
     * @param array $regex Regular Expression
     * @param array $time Time to wait before delete
     * @access public
     * @return void
     */
    public function deleteMultiRegex($regex, $time = 0)
    {
        if ($this->isSafe()) {
            $keys = $this->getKeys();
            $matchingKeys = array();

            foreach ($keys as $key) {
                if (preg_match($regex, $key)) {
                    $matchingKeys[] = $key;
                }
            }

            return $this->mem->deleteMulti($matchingKeys, $time);
        }

        return false;
    }

    /**
     * Get all the cache keys
     *
     * @access public
     * @return void
     */
    public function getKeys()
    {
        return $this->mem->getAllKeys();
    }

    /**
     * Clear all the cache
     *
     * @access public
     * @return void
     */
    public function flush()
    {
        return $this->mem->flush();
    }

    /**
     * Check if the cache is live
     *
     * @access public
     * @return True if a valid server has been added, otherwise false
     */
    public function isSafe()
    {
        return $this->safe;
    }

    /**
     * getStats returns the result of Memcache::getExtendedStats(), an associative array
     * containing arrays of server stats
     *
     * @access public
     * @return array Server stats array
     */
    public function getStats()
    {
        return $this->mem->getExtendedStats();
    }

    /**
     * @param string $prefix
     */
    public function setPrefix($prefix)
    {
        if (strlen($prefix) <= self::PREFIX_MAX_LENGTH) {
            $this->mem->setOption(\Memcached::OPT_PREFIX_KEY, $prefix);
        }
    }

    /**
     * Enable compression
     *
     * return self
     */
    public function enableCompression()
    {
        $this->mem->setOption(\Memcached::OPT_COMPRESSION, true);
    }

    /**
     * Disable compression
     *
     * return self
     */
    public function diableCompression()
    {
        $this->mem->setOption(\Memcached::OPT_COMPRESSION, false);
    }
}
