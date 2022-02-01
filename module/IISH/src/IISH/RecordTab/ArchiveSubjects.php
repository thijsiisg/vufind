<?php
namespace IISH\RecordTab;
use IISH\RecordDriver\SolrEad;

/**
 * 'Archive Subjects' record tab.
 *
 * @package IISH\RecordTab
 */
class ArchiveSubjects extends ArchiveBase {

    /**
     * Get the on-screen description for this tab.
     *
     * @return string The description.
     */
    public function getDescription() {
        return 'ArchiveSubjects';
    }

    /**
     * Is this tab active?
     *
     * @return bool True if active.
     */
    public function isActive() {
        $driver = $this->getRecordDriver();
        if ($driver instanceof SolrEad) {
            $xml = simplexml_import_dom($driver->getEAD());
            $xml->registerXPathNamespace('ead', 'urn:isbn:1-931666-22-9');
            $match = $xml->xpath('//ead:ead/ead:archdesc/ead:controlaccess/ead:geogname');

            return (($match !== false) && (count($match) > 0));
        }

        return parent::isActive();
    }
}