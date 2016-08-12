<?php
namespace IISH\Digital;
use IISH\Cache\Cacheable;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 *
 *
 * @package IISH\Digital
 */
class Loader extends Cacheable {
    /**
     * @var \VuFindHttp\HttpService
     */
    private $http;

    /**
     * @var \Zend\Config\Config
     */
    private $iishConfig;

    /**
     * Constructor.
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function __construct(ServiceLocatorInterface $serviceLocator) {
        parent::__construct($serviceLocator, 'Digital');

        $this->http = $serviceLocator->get('VuFind\Http');
        $this->iishConfig = $serviceLocator->get('VuFind\Config')->get('iish');
    }

    /**
     * The key of the cached record.
     *
     * @return string The key.
     */
    protected function getKey() {
        // TODO
    }

    /**
     * Loads the record.
     *
     * @return mixed
     */
    protected function create() {
        // TODO
    }
}