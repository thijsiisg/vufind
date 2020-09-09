<?php
namespace IISH\RecordTab;
use IISH\RecordDriver\SolrMarc;
use VuFind\RecordTab\AbstractBase;

/**
 * 'Content List' record tab.
 *
 * @package IISH\RecordTab
 */
class ContentList extends AbstractBase {

    /**
     * Get the on-screen description for this tab.
     *
     * @return string The description.
     */
    public function getDescription() {
        return 'ContentList';
    }

    /**
     * Is this tab active?
     *
     * @return bool True if active.
     */
    public function isActive() {
        $driver = $this->getRecordDriver();
        if ($driver instanceof SolrMarc)
            return !empty($driver->getEADs());

        return false;
    }
}