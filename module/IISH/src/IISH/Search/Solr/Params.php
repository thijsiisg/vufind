<?php
namespace IISH\Search\Solr;
use VuFind\Search\Solr\Params as VuFindParams;

/**
 * Solr Search Parameters.
 *
 * Override to add support for facets for archives only.
 */
class Params extends VuFindParams {

    /**
     * Initialize facet settings for archives only for the home page.
     */
    public function initHomePageFacetsForArchives() {
        $this->initFacetList('HomePageForArchives', 'HomePage_Settings');
        $this->addFilter('format:Archives');
    }

    /**
     * Initialize facet settings for the new item search.
     */
    public function initNewItemFacets() {
        $this->addFacet('format', 'Format');
    }
}
