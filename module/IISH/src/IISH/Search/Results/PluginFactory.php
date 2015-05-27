<?php
namespace IISH\Search\Results;
use Zend\ServiceManager\ServiceLocatorInterface;
use VuFind\Search\Results\PluginFactory as VuFindPluginFactory;

/**
 * Search Results Object Factory Class.
 *
 * Override to make sure the overridden Results class is used.
 *
 * @package IISH\Search\Results
 */
class PluginFactory extends VuFindPluginFactory {

    /**
     * Create a service for the specified name.
     *
     * @param ServiceLocatorInterface $serviceLocator Service locator
     * @param string                  $name           Name of service
     * @param string                  $requestedName  Unfiltered name of service
     *
     * @return object
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName) {
        if (strtolower($name) === 'solr') {
            $params = $serviceLocator->getServiceLocator()
                ->get('VuFind\SearchParamsPluginManager')->get($requestedName);
            return new \IISH\Search\Solr\Results($params);
        }

        return parent::createServiceWithName($serviceLocator, $name, $requestedName);
    }
}