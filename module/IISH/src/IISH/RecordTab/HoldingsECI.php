<?php
namespace IISH\RecordTab;
use VuFind\RecordTab\AbstractBase;

/**
 * Holdings tab for ECI records.
 *
 * @package IISH\RecordTab
 */
class HoldingsECI extends AbstractBase {

    /**
     * Get the on-screen description for this tab.
     *
     * @return string The description.
     */
    public function getDescription() {
        return 'Holdings';
    }
} 