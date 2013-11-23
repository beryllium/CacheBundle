<?php

namespace Beryllium\CacheBundle\Client;
use Beryllium\CacheBundle\Statistics;

/**
 * Completely untested and undocumented. Use at your own risk!
 *
 * Fixes appreciated!
 *
 * @package
 * @version $id$
 * @author Kevin Boyd <beryllium@beryllium.ca>
 * @license See LICENSE.md
 */
class FilecacheClient implements CacheInterface, StatsInterface
{
    protected $path = null;
    protected $hits = 0;
    protected $misses = 0;

    /**
     * @param string|null $path
     */
    public function __construct($path = null)
    {
        $this->setPath($path);

        if (!$this->isSafe()) {
            return;
        }

        if (!file_exists($this->path . '__stats')) {
            return;
        }

        $this->restoreStats();
    }

    /**
     * @param string $key
     * @return bool|mixed
     */
    public function get($key)
    {
        if (!$this->isSafe() || empty($key)) {
            return false;
        }

        // @todo Implement multi-get
        if (!file_exists($this->buildFilename($key))) {
            $this->misses++;
            return false;
        }

        $file = unserialize(file_get_contents($this->buildFilename($key)));

        if (!is_array($file) || $file['key'] != $key) {
            $this->misses++;
            return false;
        }

        if ($file['ttl'] != 0 && time() - $file['ctime'] > $file['ttl']) {
            //If key is expired, then delete file
            $this->misses++;
            $this->delete($key);
            return false;
        }

        $this->hits++;
        $this->dumpStats();

        return unserialize($file['value']);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     * @return bool|int
     */
    public function set($key, $value, $ttl = 300)
    {
        $file = array(
            'key'   => $key,
            'value' => serialize($value),
            'ttl'   => $ttl,
            'ctime' => time(),
        );

        if ($this->isSafe() && !empty($key)) {
            return (bool) file_put_contents($this->buildFilename($key), serialize($file));
        }

        return false;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function delete($key)
    {
        $filename = $this->buildFilename($key);

        if (file_exists($filename)) {
            unlink($filename);
            return true;
        }

        return false;
    }

    /**
     * @param string $path
     * @return bool
     */
    public function setPath($path)
    {
        if (empty($path)) {
            return false;
        }

        if (!is_dir($path)) {
            if (!mkdir($path)) {
                return false;
            }
        }

        if (!is_writable($path)) {
            return false;
        }

        $this->path = $path;
        if (substr($path, -1) !== "/") {
            $this->path = $path . "/";
        }

        return true;
    }

    /**
     * @return bool
     */
    public function isSafe()
    {
        return !is_null($this->path);
    }

    /**
     * @param string $key
     * @return string
     */
    protected function buildFilename($key)
    {
        return $this->path . md5($key) . '_file.cache';
    }

    /**
     * Dump statistics to cache directory
     */
    protected function dumpStats()
    {
        $stats = array(
            "hits" => $this->hits,
            "misses" => $this->misses
        );

        file_put_contents($this->buildStatsFilename(), serialize($stats));
    }

    /**
     * Restore statistics
     */
    protected function restoreStats()
    {
        $stats = unserialize(file_get_contents($this->buildStatsFilename()));
        $this->hits = $stats["hits"];
        $this->misses = $stats["misses"];
    }

    protected function buildStatsFilename()
    {
        return $this->path . '__stats';
    }

    /**
     * @return Statistics[]
     */
    public function getStats()
    {
        if (!$this->isSafe()) {
            return false;
        }

        return array("File cache" => new Statistics($this->hits, $this->misses));
    }
}
