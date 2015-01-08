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
     * Override to add a 'message of the day'.
     *
     * @return mixed
     */
    public function homeAction() {
        $viewModel = parent::homeAction();

        // Obtain the 'message of the day' and add it to the view model
        $motdLoader = new MOTDLoader($this->getServiceLocator());
        $viewModel->messageOfTheDay = $motdLoader->getMessageOfTheDay();

        return $viewModel;
    }
} 