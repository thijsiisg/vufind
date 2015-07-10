<?php
namespace IISH\RecordDriver;

use IISH\OAI\Loader as OAI;
use IISH\File\Loader as File;
use IISH\XSLT\Processor as XSLTProcessor;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Model for EAD records with a base MARC record in Solr.
 *
 * @package IISH\RecordDriver
 */
class SolrEad extends SolrMarc
{
    /**
     * @var \DOMDocument
     */
    private $ead;

    /**
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    private $serviceLocator;

    /**
     * @var string
     */
    private $siteURL;

    /**
     * @var string
     */
    private $cache_dir;

    /**
     * Constructor.
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param \Zend\Config\Config $mainConfig VuFind main configuration. (omit for
     *                                                built-in defaults)
     * @param \Zend\Config\Config $recordConfig Record-specific configuration file.
     *                                                (omit to use $mainConfig as $recordConfig)
     * @param \Zend\Config\Config $searchSettings Search-specific configuration file
     * @param \Zend\Config\Config $iishConfig IISH specific configuration.
     */
    public function __construct(ServiceLocatorInterface $serviceLocator, $mainConfig = null, $recordConfig = null,
                                $searchSettings = null, $iishConfig = null)
    {
        parent::__construct($mainConfig, $recordConfig, $searchSettings, $iishConfig);
        $this->serviceLocator = $serviceLocator;
        $this->siteURL = $serviceLocator->get('VuFind\Config')->get('config')->Site->url;
        $this->cache_dir = $serviceLocator->get('VuFind\Config')->get('config')->Cache->cache_dir;
    }

    /**
     * Returns the period.
     *
     * @return string|null The period.
     */
    public function getPeriod()
    {
        $period = $this->getFieldArray('245', array('g'), false);
        $period = count($period) > 0 ? $period[0] : null;

        return $period;
    }

    /**
     * Get the first found summary string for the record.
     *
     * @return string|null The summary.
     */
    public function getSummary()
    {
        $summary = parent::getSummary();

        return (count($summary) > 0) ? $summary[0] : null;
    }

    /**
     * Check for a pdf. Either it is on file, or it is not.
     *
     * @return true or false
     */
    public function getPDF()
    {
        $fileService = new File();
        $fileService->setFilename($this->cache_dir . '/' . $this->getUniqueID() . '.pdf');
        return $fileService->getFile();
    }

    /**
     * Returns the EAD record.
     *
     * @return \DOMDocument The EAD record.
     */
    public function getEAD()
    {
        if ($this->ead === null) {
            $oai = new OAI($this->serviceLocator);
            $oai->setId($this->getUniqueID());
            $oai->setPid($this->getOAIPid());
            $oai->setMetadataPrefix('ead');

            $this->ead = $oai->getRecord();
        }

        return $this->ead;
    }

    /**
     * Get the view by processing an XSLT with the given name for this EAD record.
     *
     * @param string $name The name of the XSLT stylesheet.
     *
     * @return string The resulting view.
     */
    public function getViewForXSLT($name)
    {
        $xslt = new XSLTProcessor(
            $this->serviceLocator,
            $this->getEAD(),
            'record-ead-' . $name . '.xsl',
            array(
                'action' => $name,
                'baseUrl' => $this->siteURL . '/Record/' . $this->getUniqueID(),
                'title' => $this->getTitle(),
            )
        );

        return $xslt->process();
    }
} 