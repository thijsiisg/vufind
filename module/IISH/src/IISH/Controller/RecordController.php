<?php
namespace IISH\Controller;
use IISH\RecordDriver\SolrEad;
use Zend\Config\Config;
use Zend\View\Model\JsonModel;
use IISH\Search\Highlighting;
use IISH\Digital\Loader as Digital;
use VuFind\Controller\RecordController as VuFindRecordController;

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
                    break;
            }
        }

        return parent::exportAction();
    }

    /**
     * Search action -- Performs a full text search within a specific record.
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function searchAction() {
        $driver = $this->loadRecord();
        $searchService = $this->getServiceLocator()->get('VuFind\Search');
        $highlighting = new Highlighting($searchService, $driver);

        $lookfor = $this->params()->fromPost('lookfor');
        $results = $highlighting->getResultsFor($lookfor);

        $viewModel = $this->createViewModel();
        $viewModel->setTemplate('search/highlighting.phtml');

        $viewModel->tagPre = $highlighting::TAG_PRE;
        $viewModel->tagPost = $highlighting::TAG_POST;
        $viewModel->results = $results;
        $viewModel->recordDriver = $driver;

        // If called via AJAX, use the Lightbox layout
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->layout()->setTemplate('layout/lightbox');
        }

        return $viewModel;
    }

    /**
     * Digital action -- Returns all available digital material.
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function digitalAction() {
        $driver = $this->loadRecord();
        $item = $this->params()->fromQuery('item');

        if ($driver instanceof SolrEad) {
           $ead = $driver->getEAD();
        }

        $digital = new Digital($this->serviceLocator);

        // TODO: Some example output:

        if ($driver->getUniqueID() === '1023831') {
            return new JsonModel(array(
                'pdf' => 'http://hdl.handle.net/10622/D4C1B7B6-D073-499D-A73A-73CEB4D793ED?locatt=view:master',
                'view' => null
            ));
        }

        if ($driver->getUniqueID() === 'ARCH03210') {
            return new JsonModel(array(
                'pdf' => 'http://hdl.handle.net/10622/ARCH03210.' . $item . '?locatt=view:pdf',
                'view' => array(
                    'mets' => 'http://hdl.handle.net/10622/ARCH03210.' . $item . '?locatt=view:mets',
                    'items' => null
                )
            ));
        }

        if ($driver->getUniqueID() === 'COLL00162') {
            return new JsonModel(array(
                'pdf' => null,
                'view' => array(
                    'mets' => 'http://hdl.handle.net/10622/COLL00162.2?locatt=view:mets',
                    'items' => array(
                        array(
                            'url' => 'http://hdl.handle.net/10622/256D7056-AC65-46E8-8CD0-E265A195732F?locatt=view:level1',
                            'contentType' => 'audio/mpeg3'
                        )
                    )
                )
            ));
        }

        if ($driver->getUniqueID() === '1502798') {
            return new JsonModel(array(
                'pdf' => 'http://hdl.handle.net/10622/4538765749248975834?locatt=view:pdf',
                'view' => array(
                    'mets' => 'http://hdl.handle.net/10622/4538765749248975834?locatt=view:mets',
                    'items' => array(
                        array(
                            'url' => 'http://hdl.handle.net/10622/1B8B599D-A11B-4861-9EBC-7CE0535FBCF2?locatt=view:level1',
                            'contentType' => 'video/mp4',
                            'stillsUrl' => 'http://hdl.handle.net/10622/1B8B599D-A11B-4861-9EBC-7CE0535FBCF2?locatt=view:level2',
                            'thumbnailUrl' => 'http://hdl.handle.net/10622/1B8B599D-A11B-4861-9EBC-7CE0535FBCF2?locatt=view:level3'
                        )
                    )
                )
            ));
        }

        // TODO: End example output

        return new JsonModel(array());
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

}