<?php

namespace Beryllium\CacheBundle\Client;
use Beryllium\CacheBundle\Statistics;

/**
 * Client interface for Memcache servers
 *
 * @uses CacheInterface
 * @package
 * @version $id$
 * @author Kevin Boyd <beryllium@beryllium.ca>
 * @license See LICENSE.md
 */
class MemcacheClient implements CacheInterface, StatsInterface
{
    protected $safe = false;
    /** @var \Memcache|null Memcache instance */
    protected $mem = null;
    protected $servers = array();
    protected $sockttl = 0.2;
    protected $compression = false;
    protected $prefix = "";

    /**
     * Constructs the cache client using an injected Memcache instance
     *
     * @access public
     */
    public function __construct($ip, $port)
    {
        //Default memcache instance
        $this->mem = new \Memcache();
        $this->addServer($ip, $port);
    }

    /**
     * Add a server to the memcache pool.
     *
     * Should really be private, or modified to handle the probeServer action itself.
     *
     * @param string $ip Location of memcache server
     * @param int $port Optional: Port number (default: 11211)
     * @return boolean
     */
    public function addServer($ip, $port = 11211)
    {
        if (!is_object($this->mem) || !$this->probeServer($ip, $port)) {
            return false;
        }
        
        $status = $this->mem->addServer($ip, $port);
        if ($status) {
            $this->safe = true;
        }

        return $status;
    }

    /**
     * Add an array of servers to the memcache pool
     *
     * Format of array:
     *
     *   $servers[] = [
     *      "ip"    => "127.0.0.1",
     *      "port"  => 11211
     *   ];
     *
     * @param array $servers See above format definition
     * @return void
     */
    public function addServers(array $servers)
    {
        foreach ($servers as $server) {
            $this->addServer($server["ip"], $server["port"]);
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
     * @param int $port Port that memcache is running on
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
     * Retrieve a value from memcache
     *
     * @param string|array $key Unique identifier or array of identifiers
     * @return mixed Requested value, or false if an error occurs
     */
    public function get($key)
    {
        if ($this->isSafe()) {
            return $this->mem->get($this->getKey($key));
        }

        return false;
    }

    /**
     * Add a value to the memcache
     *
     * @param string $key Unique key
     * @param mixed $value A value. I recommend a string, be it serialized or not - other values haven't been tested :)
     * @param int $ttl Number of seconds for the value to be valid for
     * @return boolean
     */
    public function set($key, $value, $ttl)
    {
        if ($this->isSafe()) {
            return $this->mem->set($this->getKey($key), $value, $this->compression, $ttl);
        }

        return false;
    }

    /**
     * Delete a value from the memcache
     *
     * @param string $key Unique key
     * @return boolean
     */
    public function delete($key)
    {
        if ($this->isSafe()) {
            return $this->mem->delete($this->getKey($key), 0);
        }

        return false;
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
     * Returns array of stats for every instance of caching backend available
     *
     * @return Statistics[]
     */
    public function getStats()
    {
        $result = array();

        if (!$this->isSafe()) {
            return $result;
        }
        foreach ($this->mem->getExtendedStats() as $key => $stat_array) {
            $stats = new Statistics($stat_array["get_hits"], $stat_array["get_misses"]);
            $stats->setAdditionalData(array(
                "Open connections"  => $stat_array["curr_connections"],
                "Uptime"            => $stat_array["uptime"]
            ));
            $result[$key] = $stats;
        }

        return $result;
    }

    /**
     * @param string $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    private function getKey($key) {
        return $this->prefix . $key;
    }
}
