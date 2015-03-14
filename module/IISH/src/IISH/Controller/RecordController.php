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

        $viewModel->visualmetsUrl = $this->iishConfig->VisualMets->url;
        $viewModel->visualmetsRows = $this->iishConfig->VisualMets->rows;

        return $viewModel;
    }

    /**
     * Export the record.
     *
     * Override to export records as MARCXML and EAD from the OAI and generated PDFs.
     * Redirects are only supported by VuFind using a callback.
     * TODO: (Bulk) export from other controllers.
     *
     * @return mixed
     */
    public function exportAction() {
        $driver = $this->loadRecord();
        $format = $this->params()->fromQuery('style');

        $export = $this->getServiceLocator()->get('VuFind\Export');
        if (!empty($format) && $export->recordSupportsFormat($driver, $format)) {
            switch (strtolower($format)) {
                case 'marcxml':
                case 'ead':
                case 'eci':
                    return $this->exportFromOAI($format);
                case 'pdf':
                    return $this->exportToPDF();
            }
        }

        return parent::exportAction();
    }

    /**
     * Allows for an external OAI record export.
     *
     * @param string $metadataPrefix The export format.
     *
     * @return \Zend\Http\Response
     */
    private function exportFromOAI($metadataPrefix) {
        $oaiBaseUrl = isset($this->iishConfig->OAI->baseUrl) ? $this->iishConfig->OAI->baseUrl :
            'http://api.socialhistoryservices.org/solr/all/oai';

        $oaiPid = $this->loadRecord()->getOAIPid();
        $url = $oaiBaseUrl . '?verb=GetRecord&identifier=' . urlencode($oaiPid) . '&metadataPrefix=' .
            strtolower($metadataPrefix);

        return $this->redirect()->toUrl($url);
    }

    /**
     * Export to PDF.
     * PDFs are already created, so just create the redirect link to find them.
     *
     * @return \Zend\Http\Response
     */
    private function exportToPDF() {
        $pdfLink = isset($this->iishConfig->PDF->link) ? $this->iishConfig->PDF->link : 'PDF';
        $driver = $this->loadRecord();
        $url = $this->url()->fromRoute('home') . $pdfLink . '?id=' . $driver->getUniqueID() . '&contentType=application/pdf';
        return $this->redirect()->toUrl($url);
    }
} 