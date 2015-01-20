<?php
namespace IISH\RecordDriver;
use IISH\OAI\Loader as OAI;
use IISH\XSLT\Processor as XSLTProcessor;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Model for ECI records with a base MARC record in Solr.
 *
 * @package IISH\RecordDriver
 */
class SolrEci extends SolrMarc {
    /**
     * @var \DOMDocument
     */
    private $eci;

    /**
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    private $serviceLocator;

    /**
     * Constructor.
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param \Zend\Config\Config     $mainConfig     VuFind main configuration. (omit for
     *                                                built-in defaults)
     * @param \Zend\Config\Config     $recordConfig   Record-specific configuration file.
     *                                                (omit to use $mainConfig as $recordConfig)
     * @param \Zend\Config\Config     $searchSettings Search-specific configuration file
     * @param \Zend\Config\Config     $iishConfig     IISH specific configuration.
     */
    public function __construct(ServiceLocatorInterface $serviceLocator, $mainConfig = null, $recordConfig = null,
                                $searchSettings = null, $iishConfig = null) {
        parent::__construct($mainConfig, $recordConfig, $searchSettings, $iishConfig);
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Returns the ECI record.
     *
     * @return \DOMDocument The ECI record.
     */
    public function getECI() {
        if ($this->eci === null) {
            $oai = new OAI($this->serviceLocator);
            $oai->setId($this->getUniqueID());
            $oai->setPid($this->getOAIPid());
            $oai->setMetadataPrefix('eci');

            $this->eci = $oai->getRecord();
        }

        return $this->eci;
    }

    /**
     * Get the details view by processing an XSLT for this ECI record.
     *
     * @return string The resulting view.
     */
    public function getDetailsViewFromXSLT() {
        $xslt = new XSLTProcessor(
            $this->serviceLocator,
            $this->getECI(),
            'record-eci.xsl'
        );

        return $xslt->process();
    }
} 