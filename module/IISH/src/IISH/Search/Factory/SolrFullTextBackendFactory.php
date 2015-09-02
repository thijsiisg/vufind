<?php
namespace IISH\Search\Factory;

use VuFind\Search\Factory\AbstractSolrBackendFactory;
use VuFindSearch\Backend\Solr\Connector;
use VuFindSearch\Backend\Solr\Response\Json\RecordCollectionFactory;

/**
 * Factory for the full text SOLR backend.
 *
 * @package IISH\Search\Factory
 */
class SolrFullTextBackendFactory extends AbstractSolrBackendFactory {

    public function __construct() {
        parent::__construct();
        $this->solrCore = 'fulltext';
        $this->searchConfig = 'fulltext';
        $this->searchYaml = 'fulltextsearchspecs.yaml';
    }

    /**
     * Create the SOLR backend.
     *
     * @param Connector $connector Connector
     *
     * @return \VuFindSearch\Backend\Solr\Backend
     */
    protected function createBackend(Connector $connector) {
        $backend = parent::createBackend($connector);
        $manager = $this->serviceLocator->get('VuFind\RecordDriverPluginManager');
        $callback = function ($data) use ($manager) {
            $driver = $manager->get('SolrFullText');
            $driver->setRawData($data);
            return $driver;
        };
        $factory = new RecordCollectionFactory($callback);
        $backend->setRecordCollectionFactory($factory);
        return $backend;
    }
}
