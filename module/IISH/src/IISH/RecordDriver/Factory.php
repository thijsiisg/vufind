<?php
namespace IISH\RecordDriver;
use Zend\ServiceManager\ServiceManager;

/**
 * Record Driver Factory class.
 */
class Factory {

    /**
     * Factory for the SolrMarc record driver.
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return SolrMarc
     */
    public static function getSolrMarc(ServiceManager $sm) {
        $driver = new SolrMarc(
            $sm->getServiceLocator()->get('VuFind\Config')->get('config'),
            null,
            $sm->getServiceLocator()->get('VuFind\Config')->get('searches'),
            $sm->getServiceLocator()->get('VuFind\Config')->get('iish')
        );

        $driver->attachILS(
            $sm->getServiceLocator()->get('VuFind\ILSConnection'),
            $sm->getServiceLocator()->get('VuFind\ILSHoldLogic'),
            $sm->getServiceLocator()->get('VuFind\ILSTitleHoldLogic')
        );

        return $driver;
    }

    /**
     * Factory for the SolrAv record driver.
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return SolrAv
     */
    public static function getSolrAv(ServiceManager $sm) {
        $driver = new SolrAv(
            $sm->getServiceLocator()->get('VuFind\Config')->get('config'),
            null,
            $sm->getServiceLocator()->get('VuFind\Config')->get('searches'),
            $sm->getServiceLocator()->get('VuFind\Config')->get('iish')
        );

        $driver->attachILS(
            $sm->getServiceLocator()->get('VuFind\ILSConnection'),
            $sm->getServiceLocator()->get('VuFind\ILSHoldLogic'),
            $sm->getServiceLocator()->get('VuFind\ILSTitleHoldLogic')
        );

        return $driver;
    }

    /**
     * Factory for the SolrEad record driver.
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return SolrEad
     */
    public static function getSolrEad(ServiceManager $sm) {
        $driver = new SolrEad(
            $sm->getServiceLocator()->get('VuFind\Config')->get('config'),
            null,
            $sm->getServiceLocator()->get('VuFind\Config')->get('searches'),
            $sm->getServiceLocator()->get('VuFind\Config')->get('iish')
        );

        $driver->attachILS(
            $sm->getServiceLocator()->get('VuFind\ILSConnection'),
            $sm->getServiceLocator()->get('VuFind\ILSHoldLogic'),
            $sm->getServiceLocator()->get('VuFind\ILSTitleHoldLogic')
        );

        return $driver;
    }
} 