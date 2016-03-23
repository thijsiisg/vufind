<?php
namespace IISH\Search;
use VuFind\RecordDriver\AbstractBase;
use VuFindSearch\Service;
use VuFindSearch\ParamBag;
use VuFindSearch\Query\Query;

/**
 * Returns highlighting details for a fulltext search on one specific Record.
 *
 * @package IISH\Search
 */
class Highlighting {
    const TAG_PRE = '{{{{START_HILITE}}}}';
    const TAG_POST = '{{{{END_HILITE}}}}';

    /**
     * @var Service
     */
    private $searchService;

    /**
     * @var AbstractBase
     */
    private $driver;

    /**
     * Constructor.
     *
     * @param Service      $searchService The search service.
     * @param AbstractBase $driver        The record to search within.
     */
    public function __construct(Service $searchService, AbstractBase $driver) {
        $this->searchService = $searchService;
        $this->driver = $driver;
    }

    /**
     * Find out whether there is at least one record with text indexed.
     *
     * @return bool True if the driver has text indexed for full text search.
     */
    public function hasTextIndexed() {
        $results = $this->searchService->search('SolrFullText', new Query(), 0, 1, new ParamBag(
            array(
                'fl' => 'id',
                'fq' => 'record:"' . addcslashes($this->driver->getUniqueID(), '"') . '"',
            )
        ));
        return ($results->getTotal() > 0);
    }

    /**
     * Returns the results for the given search string.
     *
     * @param string $lookfor Search string.
     *
     * @return array The results.
     */
    public function getResultsFor($lookfor) {
        if (trim($lookfor) === '' || trim($lookfor) === '""') {
            return array();
        }

        $query = new Query($lookfor);
        $params = new ParamBag(
            array(
                'fq'                          => 'record:"' . addcslashes($this->driver->getUniqueID(), '"') . '"',
                'fl'                          => 'id,record,item,page',
                'hl'                          => 'true',
                'hl.fl'                       => 'fulltext',
                'hl.snippets'                 => 5,
                'hl.tag.pre'                  => self::TAG_PRE,
                'hl.tag.post'                 => self::TAG_POST,
                'hl.usePhraseHighlighter'     => 'true',
                'hl.useFastVectorHighlighter' => 'true',
                'hl.highlightMultiTerm'       => 'true',
            )
        );

        return $this->searchService->search('SolrFullText', $query, 0, 20, $params);
    }
}