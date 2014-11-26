<?php

namespace Beryllium\CacheBundle\Tests\Client;

use Beryllium\CacheBundle\Client\MemcacheClient;

class MemcacheClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \CacheBundle\Client\MemcacheClient
     */
    private $mc;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        $this->mc = new MemcacheClient(new \Memcached());
        $this->mc->addServers(array('127.0.0.1' => 11211));
    }

    public function testSetAndGetValue()
    {
        $this->mc->set('test', 42, 1000);
        $test = $this->mc->get('test');

        $this->assertEquals(42, $test);
    }

    public function testGetKeysValue()
    {
        $this->mc->set('test', 42, 1000);
        $keys = $this->mc->getKeys();

        $this->assertContains('test', $keys);
    }

    public function testSetMultiAndGetMultiValue()
    {
        $this->mc->setMulti(
            array(
                'test_1' => 1,
                'test_2' => 2,
                'test_3' => 3,
            ),
            1000
        );

        $test = $this->mc->getMulti(array(
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
        $this->mc->set('test_1', 1, 1000);
        $this->assertContains('test_1', $this->mc->getKeys());

        $this->mc->delete('test_1');
        $this->assertNotContains('test_1', $this->mc->getKeys());
    }

    public function testDeleteMulti()
    {
        $this->mc->setMulti(
            array(
                'test_1' => 1,
                'test_2' => 2,
                'test_3' => 3,
            ),
            1000
        );

        $this->mc->deleteMulti(array('test_1', 'test_2', 'test_3'));

        $keys = $this->mc->getKeys();
        $this->assertNotContains('test_1', $keys);
        $this->assertNotContains('test_2', $keys);
        $this->assertNotContains('test_3', $keys);
    }

    public function testDeleteMultiRegex()
    {
        $this->mc->setMulti(
            array(
                'test_1' => 1,
                'test_2' => 2,
                'test_3' => 3,
            ),
            1000
        );

        $this->mc->deleteMultiRegex('/test_(\d)/');

        $keys = $this->mc->getKeys();
        $this->assertNotContains('test_1', $keys);
        $this->assertNotContains('test_2', $keys);
        $this->assertNotContains('test_3', $keys);
    }

    public function testSetPrefix()
    {
        $this->mc->setPrefix('test_');
        $this->mc->set('string', 'value', 1000);

        $test = $this->mc->getKeys();

        $this->assertContains('test_string', $test);
    }

    public function testFlushCache()
    {
        $this->mc->setMulti(
            array(
                'test_1' => 1,
                'test_2' => 2,
                'test_3' => 3,
            ),
            1000
        );
        $this->mc->flush();

        $keys = $this->mc->getKeys();

        $this->assertEmpty($keys);
    }
}
