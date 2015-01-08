<?php
namespace IISH\Controller;
use VuFind\Controller\AbstractBase;

/**
 * IISH Controller.
 *
 * @package IISH\Controller
 */
class IISHController extends AbstractBase {

    /**
     * About action.
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function aboutAction() {
        $viewModel = $this->createViewModel();
        $viewModel->setTemplate('iish/about.phtml');

        return $viewModel;
    }

    /**
     * Databases action.
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function databasesAction() {
        $viewModel = $this->createViewModel();
        $viewModel->setTemplate('iish/databases.phtml');

        return $viewModel;
    }
} 