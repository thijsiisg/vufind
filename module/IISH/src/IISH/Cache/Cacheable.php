<?php
namespace IISH\Cache;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Represents a (possible) cacheable instance.
 */
abstract class Cacheable {
    /**
     * @var \Zend\Cache\Storage\Adapter\FileSystem
     */
    private $cache;

    /**
     * Creates a new cacheable item.
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param string                  $cache
     */
    public function __construct(ServiceLocatorInterface $serviceLocator, $cache) {
        $this->cache = null;
        $cacheManager = $serviceLocator->get('VuFind\CacheManager');
        if ($cacheManager->isExistingCache($cache)) {
            $this->cache = $cacheManager->getCache($cache);
        }
    }

    /**
     * The key of the cached item.
     *
     * @return string The key.
     */
    abstract protected function getKey();

    /**
     * Creates a new instance, ready to be cached.
     *
     * @return mixed The instance to cache.
     */
    abstract protected function create();

    /**
     * Retrieves an instance from cache, or creates a new instance in the cache.
     *
     * @return mixed The instance.
     */
    protected function get() {
        $cachedInstance = $this->getFromCache();
        if ($cachedInstance !== null) {
            return $cachedInstance;
        }

        $cachedInstance = $this->create();
        if($cachedInstance !== null)
            $this->setCache($cachedInstance);

        return $cachedInstance;
    }

    /**
     * Tries to obtain an instance from the cache.
     *
     * @return mixed Instance from the cache.
     */
    private function getFromCache() {
        if ($this->cache !== null) {
            return $this->cache->getItem($this->getKey());
        }

        return null;
    }

    /**
     * Caches the given instance.
     *
     * @param mixed $instance Instance to cache.
     */
    private function setCache($instance) {
        if ($this->cache !== null) {
            $this->cache->setItem($this->getKey(), $instance);
        }
    }
} 