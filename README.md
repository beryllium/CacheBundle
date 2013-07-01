BerylliumCacheBundle for Symfony2
=================================

It's memcache. You've seen it before. Now it's injectable to the DIC, and you don't have to write all this junk yourself. And it should also work with Amazon ElasticCache, as well as the MySQL Memcache Interface (new in MySQL 5.6).

Another advantage over just using a raw memcache object is that BCB probes servers before adding them to the pool - if a server is down, your site won't be gravely impacted by the Memcache class waiting and waiting and waiting for a response.

The groundwork is also laid out for building alternate cache interfaces quickly - such as APC caching, or your own home-rolled filesystem cache.

## Configuration

### Step 1: Fetching

Using composer:

    "require": {
      "beryllium/cachebundle": "v0.2.*-dev"
    },

### Step 2: Configure the AppKernel

Add it to your AppKernel:

```php
# app/AppKernel.php

<?php

    $bundles = array(
        //...
        new Beryllium\CacheBundle\BerylliumCacheBundle(),
    );
```

### Step 3: Configure the Bundle

(This section is pending a rewrite; the configuration system for this bundle needs to be rewritten so that it can be set up in config.yml)

Configure your server list in parameters.ini:

    beryllium_cache.client.servers["127.0.0.1"] = 11211 

If you plan on using local UNIX sockets, GitHub user gierschv has contributed the ability to do this:

    beryllium_cache.client.servers["unix:///tmp/mc.sock"]=""

And then you should be good to go:
  
    $this->get( 'beryllium_cache' )->set( 'key', 'value', $ttl );
    $this->get( 'beryllium_cache' )->get( 'key' );

You might want to set up a service alias, since "$this->get( 'beryllium_cache' )" might be a bit long.

## The Command Line

For a command line report of CacheClient statistics (assuming the cache client has a ->getStats method, which is not an interface requirement), you can do the following:

    app/console cacheclient:stats

Example Output:
<pre>
Servers found: 1

Host:    127.0.0.1:11211
	Usage: 0% (0.01MB of 64MB)
	Uptime: 344976 seconds (3 days, 23 hours, 49 minutes, 36 seconds)
	Open Connections: 15
	Hits: 26
	Misses: 29
	Helpfulness: 47.27%
</pre>
Or, for extended information (the raw stats array), you can run with debugging enabled:

    app/console cacheclient:stats --debug

Help is available, although brief:

    app/console help cacheclient:stats

## The Future 

Currently there aren't any unit or functional tests. So that needs to be worked on.

More cache client implementations could be useful, if it turns out there's a demand for them.

And yes, the documentation needs to be more thorough as well. I've made some improvements, but it's still spotty at best.

Beyond that, who knows what the future might hold.

## Additional Resources

MySQL InnoDB+Memcached API: 

* http://blogs.innodb.com/wp/2011/04/get-started-with-innodb-memcached-daemon-plugin/

Amazon ElastiCache: 

* http://aws.amazon.com/elasticache/
