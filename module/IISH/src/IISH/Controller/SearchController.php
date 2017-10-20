<?php
namespace IISH\Controller;
use IISH\Statistics\SearchStatistics;
use IISH\MessageOfTheDay\Loader as MOTDLoader;
use VuFind\Controller\SearchController as VuFindSearchController;

/**
 * Redirects the user to the appropriate default VuFind action.
 *
 * @package IISH\Controller
 */
class SearchController extends VuFindSearchController {

    /**
     * Home action.
     *
     * Override to add a 'message of the day' and add archive facets.
     *
     * @return mixed
     */
    public function homeAction() {
        $viewModel = parent::homeAction();

        // Obtain the 'message of the day' and add it to the view model
        $motdLoader = new MOTDLoader($this->getServiceLocator());
        $viewModel->messageOfTheDay = $motdLoader->getMessageOfTheDay();

        // Add archives facets
        $viewModel->resultsForArchives = $this->getHomePageFacetsForArchives();

        return $viewModel;
    }

    /**
     * Results action.
     *
     * Override to make sure the facet sorting is alphabetical rather than the default (number of results).
     *
     * @return mixed
     */
    public function resultsAction() {
        if (($this->getRequest()->getQuery()->get('fulltext') === 'on') &&
            ($this->getRequest()->getQuery()->get('type') === 'AllFields')) {
            $this->getRequest()->getQuery()->set('type', 'AllFieldsFullText');
        }

        if ($this->getRequest()->getQuery()->get('facetSort') === null) {
            $this->getRequest()->getQuery()->set('facetSort', 'index');
        }

        return parent::resultsAction();
    }

    /**
     * New item search form.
     *
     * Override to add format facets.
     *
     * @return mixed
     */
    public function newitemAction() {
        $viewModel = parent::newitemAction();

        // New Item facets currently has one facet only
        $facetList = $this->getNewItemFacets()->getFacetList();
        $viewModel->formats = $facetList['format'];

        return $viewModel;
    }

    /**
     * Return a Search Results object containing homepage facet information with archives.
     * This data may come from the cache.
     *
     * @return \VuFind\Search\Solr\Results
     */
    protected function getHomePageFacetsForArchives() {
        return $this->getFacetResults('initHomePageFacetsForArchives', 'solrSearchHomeFacetsForArchives');
    }

    /**
     * Return a Search Results object containing homepage facet information with archives.
     * This data may come from the cache.
     *
     * @return \VuFind\Search\Solr\Results
     */
    protected function getNewItemFacets() {
        return $this->getFacetResults('initNewItemFacets', 'solrSearchNewItemFacets');
    }
} 