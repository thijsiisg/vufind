<?php
namespace IISH\Controller;
use VuFind\Controller\RecordController as VuFindRecordController;
use Zend\Config\Config;

/**
 * Record Controller.
 *
 * @package IISH\Controller
 */
class RecordController extends VuFindRecordController {
    /**
     * @var \Zend\Config\Config
     */
    private $iishConfig;

    /**
     * Constructor.
     *
     * @param \Zend\Config\Config $config     VuFind configuration.
     * @param \Zend\Config\Config $iishConfig IISH configuration.
     */
    public function __construct(Config $config, Config $iishConfig) {
        parent::__construct($config);
        $this->iishConfig = $iishConfig;
    }

    /**
     * Home (default) action -- forward to requested (or default) tab.
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function homeAction() {
        $viewModel = parent::homeAction();
        $driver = $this->loadRecord();

        $viewModel->pid = $driver->getUniqueID(); // TODO: replace with PID in 902$a
        $viewModel->baseUrl = '/Record/' . $driver->getUniqueID();

        $viewModel->deliveryUrl = $this->iishConfig->Delivery->url;
        $viewModel->visualmetsUrl = $this->iishConfig->VisualMets->url;
        $viewModel->visualmetsRows = $this->iishConfig->VisualMets->rows;

        return $viewModel;
    }
} 