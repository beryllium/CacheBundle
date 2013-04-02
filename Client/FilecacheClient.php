<?php

namespace Beryllium\CacheBundle\Client;

use Beryllium\CacheBundle\ClientCacheInterface;

/**
 * Completely untested and undocumented. Use at your own risk!
 *
 * Fixes appreciated!
 *
 * @uses ClientCacheInterface
 * @package
 * @version $id$
 * @author Kevin Boyd <beryllium@beryllium.ca>
 * @license See LICENSE.md
 */
class FilecacheClient implements ClientCacheInterface
{
    protected $path = null;
    public $dic = null;

    /**
     * @param string|null $path
     */
    public function __construct($path = null)
    {
        if (!empty($path) && is_dir($path) && is_writable($path)) {
            $this->path = $path;
        } else {
            $this->path = null;
        }
    }

    /**
     * @param $dic
     */
    public function setContainer($dic)
    {
        $this->dic = $dic;
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
        if (file_exists($this->buildFilename($key))) {
            $file = file_get_contents($this->buildFilename($key));
            $file = unserialize($file);

            if (!is_array($file)) {
                return false;
            } else if ($file['key'] != $key) {
                return false;
            } else {
                if (time() - $file['ctime'] > $file['ttl']) {
                    return false;
                } else {
                    return unserialize($file['value']);
                }
            }
        } else {
            return false;
        }
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
            return file_put_contents($this->buildFilename($key), serialize($file));
        }

        return false;
    }

    /**
     * @param string $key
     */
    public function delete($key)
    {
        $filename = $this->buildFilename($key);

        if (file_exists($filename)) {
            unlink($filename);
        }
    }

    /**
     * @param string $path
     * @return bool
     */
    public function setPath($path)
    {
        if (!empty($path) && is_dir($path) && is_writable($path)) {
            $this->path = $path;

            return true;
        }

        $this->path = null;

        return false;
    }

    /**
     * @return bool
     */
    public function isSafe()
    {
        if (is_null($this->path)) {
            return false;
        }

        return is_dir($this->path) && is_writable($this->path);
    }

    /**
     * @return bool
     */
    public function isFull()
    {
        //Check if the cache has exceeded its alotted size
    }

    /**
     * @param string $key
     * @return string
     */
    protected function buildFilename($key)
    {
        return $this->path . md5($key) . '_file.cache';
    }
}
