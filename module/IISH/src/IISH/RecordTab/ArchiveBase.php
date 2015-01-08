<?php
namespace IISH\RecordTab;
use VuFind\RecordTab\AbstractBase;

/**
 * Record tab archive base class.
 *
 * @package IISH\RecordTab
 */
abstract class ArchiveBase extends AbstractBase {

    /**
     * Whether this record tab also has a navigation window.
     *
     * @return bool True if this tab has navigation.
     */
    public function hasNavigation() {
        return false;
    }

    /**
     * Returns the name of th XSLT stylesheet required for this record tab.
     *
     * @return string The XSLT name.
     */
    public function getXSLTName() {
        $reflectionClass = new \ReflectionClass($this);
        $name = $reflectionClass->getShortName();

        return $name;
    }

    /**
     * Returns the name of th XSLT stylesheet required for rendering the navigation of this record tab.
     *
     * @return string|bool The XSLT name or false if this record tab has no navigation XSLT stylesheet.
     */
    public function getNavigationXSLTName() {
        return $this->getXSLTName() . 'Navigation';
    }
} 