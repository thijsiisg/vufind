<?php
namespace IISH\Cache;
use VuFind\Cache\Manager as VuFindCacheManager;
use Zend\Config\Config;
use Zend\Filter\Word\CamelCaseToUnderscore;

/**
 * Cache Manager.
 *
 * Extends the cache manager with support for additional file caches.
 * Additional file caches are created by specifying the name and TTL as key/value pairs
 * under the IISH 'Caches' configuration header.
 */
class Manager extends VuFindCacheManager {

    /**
     * Constructor.
     *
     * @param Config $config       Main VuFind configuration.
     * @param Config $searchConfig Search configuration.
     * @param Config $iishConfig   IISH configuration.
     */
    public function __construct(Config $config, Config $searchConfig, Config $iishConfig) {
        parent::__construct($config, $searchConfig);

        // Keep a reference to the default caching options
        $defaultOptions = is_array($this->defaults) ? $this->defaults : array();

        $caches = isset($iishConfig->Caches) ? $iishConfig->Caches->toArray() : array();
        foreach ($caches as $name => $ttl) {
            if (is_numeric($ttl)) {
                // The only way to set the TTL is to change the default caching values
                $this->defaults = array_merge($defaultOptions, array('ttl' => $ttl));
                $this->createFileCache($name, $this->getValidCacheDir($name));
            }
        }

        // Reset default caching options
        $this->defaults = $defaultOptions;
    }

    /**
     * Does the cache with the given name exist?
     *
     * @param string $name The name of the cache.
     *
     * @return bool True if the cache with the given name exist.
     */
    public function isExistingCache($name) {
        return isset($this->cacheSettings[$name]);
    }

    /**
     * Creates a valid folder name for a given name in the cache directory.
     *
     * @param string $name The name of the cache that needs its own cache directory.
     *
     * @return string The path where the caches for the cache with the given name are stored.
     */
    private function getValidCacheDir($name) {
        $filter = new CamelCaseToUnderscore();
        $name = $filter->filter($name);
        $name = preg_replace('/([^[:alnum:]_-]*)/', '', $name);
        $name = strtolower($name);

        return $this->getCacheDir() . $name;
    }
}