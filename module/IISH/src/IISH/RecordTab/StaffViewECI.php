<?php
namespace IISH\RecordTab;
use VuFind\RecordTab\AbstractBase;

/**
 * Staff view (ECI as MARC dump) tab.
 *
 * @package IISH\RecordTab
 */
class StaffViewECI extends AbstractBase {

    /**
     * Get the on-screen description for this tab.
     *
     * @return string
     */
    public function getDescription() {
        return 'Staff View';
    }
}