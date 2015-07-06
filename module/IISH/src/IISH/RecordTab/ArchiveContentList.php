<?php
namespace IISH\RecordTab;
use IISH\RecordDriver\SolrEad;

/**
 * 'Archive Content List' record tab.
 *
 * @package IISH\RecordTab
 */
class ArchiveContentList extends ArchiveBase {

    /**
     * Get the on-screen description for this tab.
     *
     * @return string The description.
     */
    public function getDescription() {
        return 'ArchiveContentList';
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
            $match = $xml->xpath('//ead:ead/ead:archdesc/ead:dsc/ead:c01');

            return (($match !== false) && (count($match) > 0));
        }

        return parent::isActive();
    }

    /**
     * Whether this record tab also has a navigation window.
     *
     * @return bool True if this tab has navigation.
     */
    public function hasNavigation() {
        return true;
    }
}