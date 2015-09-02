<?php
namespace IISH\Search\SolrFullText;

/**
 * Solr Full Text Search Parameters
 *
 * @package IISH\Search\SolrFullText
 */
class Results extends \VuFind\Search\Solr\Results {

    /**
     * Constructor
     *
     * @param \VuFind\Search\Base\Params $params Object representing user search
     * parameters.
     */
    public function __construct(\VuFind\Search\Base\Params $params) {
        parent::__construct($params);
        $this->backendId = 'SolrFullText';
    }
}