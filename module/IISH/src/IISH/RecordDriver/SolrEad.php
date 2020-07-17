<?php
namespace IISH\RecordDriver;

use IISH\Content\IISHNetwork;
use IISH\OAI\Loader as OAI;
use IISH\File\Loader as File;
use IISH\XSLT\Processor as XSLTProcessor;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Model for EAD records with a base MARC record in Solr.
 *
 * @package IISH\RecordDriver
 */
class SolrEad extends SolrMarc {
    /**
     * @var \DOMDocument
     */
    private $ead;

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
                                $searchSettings = null, $iishConfig = null) {
        parent::__construct($serviceLocator, $mainConfig, $recordConfig, $searchSettings, $iishConfig);
        $this->siteURL = $serviceLocator->get('VuFind\Config')->get('config')->Site->url;
        $this->cache_dir = $serviceLocator->get('VuFind\Config')->get('config')->Cache->cache_dir;
    }

    /**
     * Add an extension to the title.
     * In the case of EAD, no extension to the title.
     *
     * @param string $title The original title.
     * @return string An extension of the title.
     */
    public function getTitleExtension($title) {
        return self::escape($title);
    }

    /**
     * Returns the period.
     *
     * @return string|null The period.
     */
    public function getPeriod() {
        $period = $this->getFieldArray('245', array('g'), false);
        $period = count($period) > 0 ? $period[0] : null;

        return $period;
    }

    /**
     * Get the first found summary string for the record.
     *
     * @return string|null The summary.
     */
    public function getSummary() {
        $summary = parent::getSummary();

        return (count($summary) > 0) ? $summary[0] : null;
    }

    /**
     * Returns the extent.
     *
     * @return string|null The extent.
     */
    public function getExtent() {
        $extent = $this->getFieldArray('300', array('a'), false);
        $extent = count($extent) > 0 ? $extent[0] : null;

        return $extent;
    }

    /**
     * Check for a pdf. Either it is on file, or it is not.
     *
     * @return true or false
     */
    public function getPDF() {
        $fileService = new File();
        $fileService->setFilename($this->cache_dir . '/pdf/' . $this->getUniqueID() . '.pdf');
        return $fileService->getFile();
    }

    /**
     * Returns the EAD record.
     *
     * @return \DOMDocument The EAD record.
     */
    public function getEAD() {
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
    public function getViewForXSLT($name) {
        $xslt = new XSLTProcessor(
            $this->serviceLocator,
            $this->getEAD(),
            'record-ead-' . $name . '.xsl',
            array(
                'id' => $this->getUniqueID(),
                'action' => $name,
                'baseUrl' => $this->siteURL . '/Record/' . $this->getUniqueID(),
                'title' => preg_replace('/[\'"]/', '`', $this->getTitle()),
				'isInternal' => $this->iishNetwork->isInternal()
            )
        );

        return $xslt->process();
    }

    /**
     * Returns one of three things: a full URL to a thumbnail preview of the record
     * if an image is available in an external system; an array of parameters to
     * send to VuFind's internal cover generator if no fixed URL exists; or false
     * if no thumbnail can be generated.
     *
     * Override to add temporarily support for audio thumbnails if there is audio digitally available.
     *
     * @param string $size Size of thumbnail (small, medium or large -- small is default).
     *
     * @return string|array|bool
     */
    public function getThumbnail($size = 'small') {
        $thumbnail = parent::getThumbnail($size);

        if (array_search('Sound documents', $this->getFieldArray('655', 'a')) !== false) {
            $thumbnail = is_array($thumbnail) ? $thumbnail : array();
            $thumbnail['audio'] = 'audio';
        }

        return $thumbnail;
    }
} 