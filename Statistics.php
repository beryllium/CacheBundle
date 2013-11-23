<?php

namespace Beryllium\CacheBundle;

/**
 * Class for unified statistics control
 *
 * @package
 * @version $id$
 * @author Yaroslav Nechaev <mail@remper.ru>
 * @license See LICENSE.md
 */
class Statistics
{
    protected $hits;
    protected $misses;
    protected $additional;

    /**
     * Create statistics object based on raw data
     *
     * @param $hits
     * @param $misses
     */
    public function __construct($hits, $misses)
    {
        $this->hits = $hits;
        $this->misses = $misses;
        $this->additional = array();
    }

    /**
     * Hits
     *
     * @return int
     */
    public function getHits()
    {
        return $this->hits;
    }

    /**
     * Misses
     *
     * @return int
     */
    public function getMisses()
    {
        return $this->misses;
    }

    /**
     * Get helpfulness percentage
     *
     * @return string
     */
    public function getHelpfulness()
    {
        if ($this->hits + $this->misses == 0) {
            return '0.00%';
        }

        return number_format(($this->hits / ($this->hits + $this->misses)) * 100, 2) . '%';
    }

    /**
     * @param array $additional
     */
    public function setAdditionalData($additional)
    {
        $this->additional = $additional;
    }

    /**
     * @return array
     */
    public function getAdditionalData()
    {
        return $this->additional;
    }

    /**
     * @return array
     */
    public function getFormattedArray()
    {
        return array_merge($this->getAdditionalData(), array(
            'Hits' => $this->getHits(),
            'Misses' => $this->getMisses(),
            'Helpfulness' => $this->getHelpfulness()
        ));
    }
}