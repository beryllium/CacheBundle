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
	public function __construct( CacheClientInterface $client = null );
	public function get( $key );
	public function set( $key, $value, $ttl );
  public function delete( $key );
	public function isSafe();
}
