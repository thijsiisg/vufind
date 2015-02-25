<?php
namespace IISH\Search\Params;
use IISH\Search\Solr\Params;
use Zend\ServiceManager\ServiceLocatorInterface;
use VuFind\Search\Params\PluginFactory as VuFindPluginFactory;

/**
 * Search params plugin factory.
 *
 * Override to make sure the overridden Params class is used.
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
            $options = $serviceLocator->getServiceLocator()
                ->get('VuFind\SearchOptionsPluginManager')
                ->get($requestedName);

            return new Params(clone($options), $serviceLocator->getServiceLocator()->get('VuFind\Config'));
        }

        return parent::createServiceWithName($serviceLocator, $name, $requestedName);
    }
}