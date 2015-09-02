<?php
namespace IISH\RecordDriver;
use \VuFind\RecordDriver\SolrDefault;

/**
 * Model for Solr full text records.
 *
 * @package IISH\RecordDriver
 */
class SolrFullText extends SolrDefault {

    /**
     * Constructor
     *
     * @param \Zend\Config\Config $mainConfig     VuFind main configuration (omit for built-in defaults)
     * @param \Zend\Config\Config $recordConfig   Record-specific configuration file
     *                                            (omit to use $mainConfig as $recordConfig)
     * @param \Zend\Config\Config $searchSettings Search-specific configuration file
     */
    public function __construct($mainConfig = null, $recordConfig = null, $searchSettings = null) {
        parent::__construct($mainConfig, $recordConfig, $searchSettings);
        $this->highlight = true;
    }


    /**
     * Get the full text of the record.
     *
     * @return string
     */
    public function getFullText() {
        return isset($this->fields['fulltext']) ? $this->fields['fulltext'] : '';
    }

    /**
     * Get the biblio record id of which the text snippet belongs to.
     *
     * @return string
     */
    public function getRecordId() {
        return isset($this->fields['record']) ? $this->fields['record'] : null;
    }

    /**
     * Get the item of which the text snippet belongs to.
     *
     * @return string
     */
    public function getItem() {
        return isset($this->fields['item']) ? $this->fields['item'] : null;
    }

    /**
     * Get the page of which the text snippet belongs to.
     *
     * @return string
     */
    public function getPage() {
        return isset($this->fields['page']) ? $this->fields['page'] : null;
    }

    /**
     * Get a highlighted full text snippets, if available.
     *
     * @return string[]
     */
    public function getHighlightedText() {
        return (isset($this->highlightDetails['fulltext'])) ? $this->highlightDetails['fulltext'] : array();
    }
}
