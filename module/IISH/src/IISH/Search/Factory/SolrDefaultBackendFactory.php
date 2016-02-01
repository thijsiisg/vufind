<?php
namespace IISH\Search\Factory;
use VuFindSearch\Backend\Solr\Backend;
use VuFindSearch\Backend\Solr\Connector;
use VuFind\Search\Factory\SolrDefaultBackendFactory as VuFindSolrDefaultBackendFactory;

/**
 * Factory for the default SOLR backend.
 *
 * Override to limit the fields to return.
 *
 * @package IISH\Search\Factory
 */
class SolrDefaultBackendFactory extends VuFindSolrDefaultBackendFactory {

    /**
     * Create the SOLR backend.
     *
     * Override to limit the fields to return.
     * We don't want to return the large 'fulltext' field.
     *
     * @param Connector $connector Connector
     *
     * @return Backend
     */
    protected function createBackend(Connector $connector) {
        // Solr does not support excluding fields, so we have to specify a long list of the field we want
        // Try to use a wildcard (*) when possible
        $connector
            ->getMap()
            ->getParameters('select', 'defaults')
            ->set('fl', 'id,fullrecord,marc_error,spelling*,institution,' .
                'collection,building,language,format,author*,title*,physical,publish*,edition,description,' .
                'contents,url,thumbnail,lccn,ctrlnum,isbn,issn,oclc_num,callnumber*,dewey*,date*,series*,' .
                'topic*,genre*,geographic*,era*,illustrated,long_lat,downloadable,no_text,mets_barcodes,' .
                'collector*,classification,*facet,container*,*hierarchy*,recordtype,*indexed,score');
        return parent::createBackend($connector);
    }
}