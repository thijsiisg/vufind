<?php
namespace IISH\Service;
use Zend\ServiceManager\ServiceManager;

/**
 * Factory for various overridden top-level VuFind services.
 *
 * @package IISH\Service
 */
class Factory {

    /**
     * Construct the cache manager.
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return \IISH\Cache\Manager
     */
    public static function getCacheManager(ServiceManager $sm) {
        return new \IISH\Cache\Manager(
            $sm->get('VuFind\Config')->get('config'),
            $sm->get('VuFind\Config')->get('searches'),
            $sm->get('VuFind\Config')->get('iish')
        );
    }
} 