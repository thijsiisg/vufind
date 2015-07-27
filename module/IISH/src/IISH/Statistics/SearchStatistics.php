<?php
namespace IISH\Statistics;

use IISH\Cache\Cacheable;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Predicate\Predicate;

/**
 * Obtain cached search statistics.
 *
 * @package IISH\Statistics
 */
class SearchStatistics extends Cacheable {
    /**
     * @var \VuFind\Db\Table\UserStatsFields
     */
    private $userStatsFields;

    /**
     * Constructor.
     * For the creation of cacheable search statistics.
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function __construct(ServiceLocatorInterface $serviceLocator) {
        parent::__construct($serviceLocator, 'SearchStatistics');

        $tableManager = $serviceLocator->get('VuFind\DbTablePluginManager');
        $this->userStatsFields = $tableManager->get('UserStatsFields');
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
     * @return array The instance to cache.
     */
    protected function create() {
        $resultSet = $this->userStatsFields->select(function (Select $select) {
            $select->columns(array(
                'value',
                'count' => new Expression('count(value)')
            ));

            $predicate = new Predicate();
            $select->where(
                $predicate
                    ->equalTo('field', 'phrase')
                    ->AND
                    ->notEqualTo('value', '')
            );

            $select->group('value');
            $select->order('count desc');
            $select->limit(10);
        });

        return $resultSet->toArray();
    }
}