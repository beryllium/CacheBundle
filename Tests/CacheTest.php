<?php

namespace Beryllium\CacheBundle\Tests;

use Beryllium\CacheBundle\Client\MemcacheClient;
use Beryllium\CacheBundle\Cache;

class CacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Beryllium\CacheBundle\Cache
     */
    private $client;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        $mc = new MemcacheClient(new \Memcached());
        $mc->addServers(array('127.0.0.1' => 11211));

        $this->client = new Cache($mc);
    }

    public function testSetAndGetValue()
    {
        $this->client->set('test', 42, 1000);
        $test = $this->client->get('test');

        $this->assertEquals(42, $test);
    }

    public function testGetKeysValue()
    {
        $this->client->set('test', 42, 1000);
        $keys = $this->client->getKeys();

        $this->assertContains('test', $keys);
    }

    public function testSetMultiAndGetMultiValue()
    {
        $this->client->setMulti(
            array(
                'test_1' => 1,
                'test_2' => 2,
                'test_3' => 3,
            ),
            1000
        );

        $test = $this->client->get(array(
            'test_1',
            'test_2',
            'test_3',
        ));

        $this->assertArrayHasKey('test_1', $test);
        $this->assertContains(1, $test);

        $this->assertArrayHasKey('test_2', $test);
        $this->assertContains(2, $test);

        $this->assertArrayHasKey('test_3', $test);
        $this->assertContains(3, $test);
    }

    public function testDelete()
    {
        $this->client->set('test_1', 1, 1000);
        $this->assertContains('test_1', $this->client->getKeys());

        $this->client->delete('test_1');
        $this->assertNotContains('test_1', $this->client->getKeys());
    }

    public function testDeleteMulti()
    {
        $this->client->setMulti(
            array(
                'test_1' => 1,
                'test_2' => 2,
                'test_3' => 3,
            ),
            1000
        );

        $this->client->delete(array('test_1', 'test_2', 'test_3'));

        $keys = $this->client->getKeys();
        $this->assertNotContains('test_1', $keys);
        $this->assertNotContains('test_2', $keys);
        $this->assertNotContains('test_3', $keys);
    }

    public function testDeleteMultiRegex()
    {
        $this->client->setMulti(
            array(
                'test_1' => 1,
                'test_2' => 2,
                'test_3' => 3,
            ),
            1000
        );

        $this->client->deleteRegex('/test_(\d)/');

        $keys = $this->client->getKeys();
        $this->assertNotContains('test_1', $keys);
        $this->assertNotContains('test_2', $keys);
        $this->assertNotContains('test_3', $keys);
    }

    public function testFlushCache()
    {
        $this->client->setMulti(
            array(
                'test_1' => 1,
                'test_2' => 2,
                'test_3' => 3,
            ),
            1000
        );
        $this->client->flush();

        $keys = $this->client->getKeys();

        $this->assertEmpty($keys);
    }
}
