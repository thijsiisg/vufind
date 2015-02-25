<?php
namespace IISH\Content\Covers;
use Zend\ServiceManager\ServiceManager;

/**
 * Factory for instantiating content loaders.
 *
 * @package IISH\Content\Covers
 */
class Factory {

    /**
     * Create a IISH cover content loader.
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return IISH
     */
    public static function getIISH(ServiceManager $sm) {
        $config = $sm->getServiceLocator()->get('VuFind\Config')->get('iish');

        return new IISH($config);
    }
}
