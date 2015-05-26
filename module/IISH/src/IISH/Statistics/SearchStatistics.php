<?php
namespace IISH\Statistics;
use IISH\Cache\Cacheable;
use VuFind\Statistics\Search as SearchStats;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Obtain cached search statistics.
 *
 * @package IISH\Statistics
 */
class SearchStatistics extends Cacheable {
    /**
     * @var SearchStats
     */
    private $searchStats;

    /**
     * Constructor.
     * For the creation of cacheable search statistics.
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function __construct(ServiceLocatorInterface $serviceLocator) {
        parent::__construct($serviceLocator, 'SearchStatistics');
        $this->searchStats = $serviceLocator->get('VuFind\SearchStats');
    }

    /**
     * Obtain the search statistics.
     *
     * @return array The search statistics.
     */
    public function getStats() {
        return $this->get();
    }

    /**
     * The key of the cached item.
     *
     * @return string The key.
     */
    protected function getKey() {
        return 'searchstats';
    }

    /**
     * Creates a new instance, ready to be cached.
     *
     * @return mixed The instance to cache.
     */
    protected function create() {
        $stats = $this->searchStats->getStatsSummary(10, false);
        return $stats['top'];
    }
}