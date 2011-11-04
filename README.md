# BerylliumCacheBundle for Symfony2 #

It's memcache. You've seen it before. Now it's injectable to the DIC, and you don't have to write all this junk yourself.

Of course, if you need it, you probably already have. And if you have, you've probably done it in a better way.

Ah well. Maybe this will help a few people, maybe it won't, who knows - I'm just in it for the fame and fortune, really. ;-)

The groundwork is also laid out for building alternate cache interfaces quickly - such as APC caching, or your own home-rolled filesystem cache.

# Configuration #

Add it to your AppKernel (this example assumes that CacheBundle is located in src/Beryllium/CacheBundle):

    $bundles = array(
        //...
        new Beryllium\CacheBundle\BerylliumCacheBundle(),
    );

Configure your server list in parameters.ini:

    beryllium_memcache.servers["127.0.0.1"] = 11211 

And then you should be good to go:
  
    $this->get( 'beryllium_cache' )->set( 'key', 'value', $ttl );
    $this->get( 'beryllium_cache' )->get( 'key' );

You might want to set up a service alias, since "$this->get( 'beryllium_cache' )" might be a bit long.

# The Future #
Currently there aren't any unit or functional tests. So that needs to be worked on.

More cache client implementations could be useful, if it turns out there's a demand for them.

And yes, the documentation needs to be more thorough as well. For example, there ought to be documentation on how to add it to the deps file and have it placed in the Vendor folder instead of Src.

Beyond that, who knows :)
