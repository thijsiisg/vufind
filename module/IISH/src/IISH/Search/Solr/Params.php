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
     * Pull the search parameters.
     *
     * Override to add support for changing the facet sorting.
     *
     * @param \Zend\StdLib\Parameters $request Parameter object representing user request.
     */
    public function initFromRequest($request) {
        parent::initFromRequest($request);

        $facetSort = $request->get('facetSort');
        if (($facetSort !== null) && (($facetSort === 'count') || ($facetSort === 'index'))) {
            $this->setFacetSort($facetSort);
        }
    }

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

    /**
     * Get Facet Sorting.
     *
     * @return string The sorting action value.
     */
    public function getFacetSort() {
        return $this->facetSort;
    }
}
