<?php
namespace IISH\Search\Solr;
use IISH\Search\UrlQueryHelper;
use VuFind\Search\Solr\Results as VuFindSolrResults;

/**
 * Solr Search Parameters.
 *
 * Override to add support for new UrlQueryHelper.
 *
 * @package IISH\Search\Solr
 */
class Results extends VuFindSolrResults {

    /**
     * Get the URL helper for this object.
     * Override to use new UrlQueryHelper.
     *
     * @return UrlQueryHelper
     */
    public function getUrlQuery() {
        // Set up URL helper:
        if (!isset($this->helpers['urlQuery'])) {
            $this->helpers['urlQuery'] = new UrlQueryHelper($this->getParams());
        }
        return $this->helpers['urlQuery'];
    }
}
