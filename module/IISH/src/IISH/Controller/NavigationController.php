<?php
namespace IISH\Controller;
use VuFind\RecordTab\TabInterface;
use VuFind\Controller\AbstractRecord;

/**
 * Provides navigational links for the contents of the opened tab.
 *
 * @package IISH\Controller
 */
class NavigationController extends AbstractRecord {
    /**
     * @var TabInterface
     */
    private $tab;

    /**
     * Home (default) action -- load the navigation for the requested tab of the requested record.
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function homeAction() {
        $viewModel = $this->createViewModel();
        $tab = $this->loadTab();

        $viewModel->driver = $this->loadRecord();
        $viewModel->tab = $tab;

        $reflectionClass = new \ReflectionClass($tab);
        $name = $reflectionClass->getShortName();
        $template = 'RecordTab/Navigation/' . strtolower($name) . '.phtml';
        $viewModel->setTemplate($template);

        // If called via AJAX, use the Lightbox layout
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->layout()->setTemplate('layout/lightbox');
        }

        return $viewModel;
    }

    /**
     * Load the tab requested by the user.
     *
     * @return TabInterface The tab interface.
     */
    private function loadTab() {
        // Only load the tab if it has not already been loaded
        if (!is_object($this->tab)) {
            $currentTab = strtolower($this->params()->fromRoute('tab', $this->getDefaultTab()));
            foreach ($this->getAllTabs() as $name => $tab) {
                if ($currentTab === strtolower($name)) {
                    $this->tab = $tab;
                    break;
                }
            }
        }

        return $this->tab;
    }
} 