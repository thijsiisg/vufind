<?php
namespace IISH\RecordTab;

use IISH\RecordDriver\SolrEad;

/**
 * 'Archive Content and Structure' record tab.
 *
 * @package IISH\RecordTab
 */
class ArchiveContentAndStructure extends ArchiveBase {

    /**
     * Get the on-screen description for this tab.
     *
     * @return string The description.
     */
    public function getDescription() {
        return 'ArchiveContentAndStructure';
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
            $match = $xml->xpath('//ead:ead/ead:archdesc/ead:bioghist|' .
                '//ead:ead/ead:archdesc' .
                '[ead:scopecontent|ead:arrangement|ead:processinfo|ead:altformavail|ead:originalsloc|ead:relatedmaterial]');
            return (($match !== false) && (count($match) > 0));
        }

        return parent::isActive();
    }
}