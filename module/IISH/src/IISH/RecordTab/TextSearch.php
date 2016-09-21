<?php
namespace IISH\RecordTab;
use VuFind\RecordTab\AbstractBase;

/**
 * A tab that allows users to search through the text.
 *
 * @package IISH\RecordTab
 */
class TextSearch extends AbstractBase {

    /**
     * Get the on-screen description for this tab.
     *
     * @return string The description.
     */
    public function getDescription() {
        return 'Search in text';
    }

    /**
     * Is this tab active?
     *
     * @return bool
     */
    public function isActive() {
        $driver = $this->getRecordDriver();
        if ($driver instanceof \IISH\RecordDriver\SolrMarc) {
            return $driver->hasTextIndexed();
        }
        return false;
    }
}