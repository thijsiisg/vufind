<?php
namespace IISH\RecordDriver;
use IISH\OAI\Loader as OAI;
use IISH\XSLT\Processor as XSLTProcessor;

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