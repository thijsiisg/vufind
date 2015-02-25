<?php
namespace IISH\Controller;
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
     * Return a Search Results object containing homepage facet information with archives.
     * This data may come from the cache.
     *
     * @return \VuFind\Search\Solr\Results
     */
    protected function getHomePageFacetsForArchives() {
        return $this->getFacetResults('initHomePageFacetsForArchives', 'solrSearchHomeFacetsForArchives');
    }
} 