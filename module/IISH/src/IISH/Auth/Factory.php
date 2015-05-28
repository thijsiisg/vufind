<?php
namespace IISH\Auth;
use Zend\ServiceManager\ServiceManager;
use VuFind\Auth\Factory as VuFindFactory;

/**
 * Factory for authentication services.
 *
 * Override to make use of new manager.
 *
 * @package IISH\Auth
 */
class Factory extends VuFindFactory {

    /**
     * Construct the authentication manager.
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return Manager
     */
    public static function getManager(ServiceManager $sm) {
        return new Manager($sm->get('VuFind\Config')->get('config'));
    }
}