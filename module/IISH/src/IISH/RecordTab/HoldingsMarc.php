<?php
namespace IISH\RecordTab;
use VuFind\RecordTab\AbstractBase;

/**
 * Holdings tab for MARC records.
 *
 * @package IISH\RecordTab
 */
class HoldingsMarc extends AbstractBase {

    /**
     * Get the on-screen description for this tab.
     *
     * @return string The description.
     */
    public function getDescription() {
        return 'Holdings';
    }
}