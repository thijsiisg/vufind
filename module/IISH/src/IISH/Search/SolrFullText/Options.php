<?php
namespace IISH\Search\SolrFullText;

/**
 * Solr Full Text Search Options
 *
 * @package IISH\Search\SolrFullText
 */
class Options extends \VuFind\Search\Solr\Options {

    /**
     * Constructor
     *
     * @param \VuFind\Config\PluginManager $configLoader Config loader
     */
    public function __construct(\VuFind\Config\PluginManager $configLoader) {
        $this->highlight = true;
        parent::__construct($configLoader);
    }

    /**
     * Return the route name for the search results action.
     *
     * @return string|bool
     */
    public function getSearchAction() {
        // Not currently supported:
        return false;
    }

    /**
     * Return the route name of the action used for performing advanced searches.
     * Returns false if the feature is not supported.
     *
     * @return string|bool
     */
    public function getAdvancedSearchAction() {
        // Not currently supported:
        return false;
    }

    /**
     * Does this search option support the cart/book bag?
     *
     * @return bool
     */
    public function supportsCart() {
        // Not currently supported
        return false;
    }
}