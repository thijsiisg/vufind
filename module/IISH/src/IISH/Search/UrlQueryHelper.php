<?php
namespace IISH\Search;
use VuFind\Search\UrlQueryHelper as VuFindUrlQueryHelper;

/**
 * Class to help build URLs and forms in the view based on search settings.
 * Override to add support for facet sorting.
 *
 * @package IISH\Search
 */
class UrlQueryHelper extends VuFindUrlQueryHelper {

    /**
     * Returns the facet sort parameter.
     *
     * @return string The facet sort parameter.
     */
    public function getFacetSort() {
        $params = $this->getParamArray();
        return isset($params['facetSort']) ? $params['facetSort'] : '';
    }

    /**
     * Change the facet sort parameter to 'count'.
     *
     * @return string The new query string.
     */
    public function setFacetSortCount() {
        $params = $this->getParamArray();
        $params['facetSort'] = 'count';

        return '?' . $this->buildQueryString($params);
    }

    /**
     * Change the facet sort parameter to 'index'.
     *
     * @return string The new query string.
     */
    public function setFacetSortIndex() {
        $params = $this->getParamArray();
        $params['facetSort'] = 'index';

        return '?' . $this->buildQueryString($params);
    }

    /**
     * Get an array of URL parameters.
     *
     * @return array
     */
    protected function getParamArray() {
        $params = parent::getParamArray();

        if ($this->params instanceof \IISH\Search\Solr\Params) {
            $params['facetSort'] = $this->params->getFacetSort();
        }

        return $params;
    }
}