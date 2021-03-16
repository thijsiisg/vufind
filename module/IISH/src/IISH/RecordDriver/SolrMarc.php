<?php
namespace IISH\RecordDriver;
use Zend\ServiceManager\ServiceLocatorInterface;
use VuFind\RecordDriver\SolrMarc as VuFindSolrMarc;
use IISH\Content\IISHNetwork;

/**
 * Model for MARC records in Solr.
 *
 * @package IISH\RecordDriver
 */
class SolrMarc extends VuFindSolrMarc {
    /**
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * @var \Zend\Config\Config
     */
    protected $iishConfig;

    /**
     * @var \IISH\Content\IISHNetwork
     */
    protected $iishNetwork;

    /**
     * Constructor.
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param \Zend\Config\Config $mainConfig VuFind main configuration. (omit for
     *                                            built-in defaults)
     * @param \Zend\Config\Config $recordConfig Record-specific configuration file.
     *                                            (omit to use $mainConfig as $recordConfig)
     * @param \Zend\Config\Config $searchSettings Search-specific configuration file
     * @param \Zend\Config\Config $iishConfig IISH specific configuration.
     */
    public function __construct(ServiceLocatorInterface $serviceLocator, $mainConfig = null, $recordConfig = null,
                                $searchSettings = null, $iishConfig = null) {
        parent::__construct($mainConfig, $recordConfig, $searchSettings);
        $this->serviceLocator = $serviceLocator;
        $this->iishConfig = $iishConfig;
        $this->iishNetwork = new IISHNetwork($this->iishConfig);
    }

    /**
     * We don't use the built-in AJAX status lookups.
     */
    public function supportsAjaxStatus() {
        return false;
    }

    /**
     * Get the main author of the record.
     * Override to normalize the value.
     *
     * @return string
     */
    public function getPrimaryAuthor() {
        return self::normalize(parent::getPrimaryAuthor());
    }

    /**
     * Get the full title of the record.
     * If the title ends with a single character, remove it. (Usually /)
     *
     * @return string The title.
     */
    public function getTitle() {
        return $this->origineleTaal('245-01') . $this->getTitleExtension(parent::getTitle());
    }

    /**
     * Add an extension to the title.
     *
     * @param string $title The original title.
     * @return string An extension of the title.
     */
    public function getTitleExtension($title) {
        $append = null;
        $fields = array('245' => array('b'), '710' => array('a'));

        foreach ($fields as $field => $subfield) {
            $append = $this->getFirstFieldValue($field, $subfield);
            if ($append && (strpos($title, $append) === false)) {
                return self::escape($title . ' ' . $append);
            }
        }

        return self::escape($title);
    }

    /**
     * Pak de originele taal. Deze is altijd te vinden in de 880 velden
     *
     * Voorbeeld
     * 100    1         |6 880-02    |a TEXT_A
     * 245    1    0    |6 880-01    |a TEXT_B
     * 880    1    0    |6 100-02/$1 |a TEXT_D
     * 880    1    0    |6 245-01/$  |a TEXT_C
     *
     * dan wordt in de weergave:
     * TEXT_D = TEXT_A
     * TEXT_C = TEXT_B
     *
     * @param string $tag The tag and index. E.g. 245-1
     * @return string An prepend of the title.
     */
    public function origineleTaal($tag_index)
    {
        $field_880_6a = $this->getFieldArray('880', array('6', 'a'), false);
        if ($field_880_6a) {
            // E.g. ( [0] => 245-01/$ [1] => TEXT_D. [2] => 100-02/$1 [3] => TEXT_C. )
            for ($i = 0; $i < count($field_880_6a); $i += 2) {
                $element = $field_880_6a[$i];
                $needle = explode('/', $element, 2)[0]; // 245-01/$ => 245-01
                if ($needle == $tag_index) {
                    return self::normalize($field_880_6a[$i + 1]) . ' = ';
                }
            }
        }

        return ''; // identity
    }

    /**
     * Get the short (pre-subtitle) title of the record.
     * If the title ends with a single character, remove it. (Usually /)
     *
     * @return string The short title escaped.
     */
    public function getShortTitle() {
        return $this->origineleTaal('245-01') . self::escape(parent::getShortTitle());
    }

    /**
     * Get text that can be displayed to represent this record in breadcrumbs.
     *
     * Override to make sure we use the escaped short title.
     *
     * @return string Breadcrumb text to represent this record.
     */
    public function getBreadcrumb() {
        return $this->getShortTitle();
    }

    /**
     * True if we have a link to downloadable content.
     *
     * @return bool
     */
    public function getDownloadable() {
        if (isset($this->fields['downloadable'])) {
            return $this->fields['downloadable'];
        }

        return false;
    }

    /**
     * True if we have full text indexed.
     *
     * @return bool
     */
    public function hasFullText() {
        if (isset($this->fields['no_text'])) {
            return !$this->fields['no_text'];
        }
        return false;
    }

    /**
     * Returns the collector.
     *
     * @return string|null The collector.
     */
    public function getCollector() {
        if (isset($this->fields['collector'])) {
            return self::normalize($this->fields['collector']);
        }

        return null;
    }

    /**
     * Retrieve the publication status.
     * Defaults to: 'closed' with 852$p=30051* ; 'open' in any other case.
     *
     * @return string The publication status.
     */
    public function getPublicationStatus() {
        $publicationStatus = $this->getFirstFieldValue('542', array('m'));
        if (empty($publicationStatus)) {
            $p = $this->getFirstFieldValue('852', array('p'));
            $publicationStatus = (strpos($p, '30051') === false) ? 'open' : 'closed';
        }

        return $publicationStatus;
    }

    /**
     * Does this record have audio content available?
     *
     * @return bool True if there is audio content.
     */
    public function hasAudio() {
        return (strpos($this->getFirstFieldValue('856', array('q')), 'audio') === 0);
    }

    /**
     * Does this record have video content available?
     *
     * @return bool True if there is video content.
     */
    public function hasVideo() {
        return (strpos($this->getFirstFieldValue('856', array('q')), 'video') === 0);
    }

    /**
     * Which barcodes of this record have METS?
     *
     * @return string[] All barcodes with METS.
     */
    public function getBarcodesWithManifest() {
        if (isset($this->fields['mets_barcodes'])) {
            return $this->fields['mets_barcodes'];
        }
        return array();
    }

    /**
     * Tries to find the authority for the specified MARC field.
     * If a value is given, the authority for that value is returned if found.
     * Otherwise the first found authority is returned instead.
     * If a subfield is given, the value is matched with the value of the given subfield.
     * Otherwise the first subfield (a) is used instead.
     *
     * @param string      $field    The MARC field
     * @param string|null $value    The value of the given subfield for matching
     * @param string|null $subfield The MARC subfield
     *
     * @return string|null The authority or null if not found
     */
    public function getAuthorityForField($field, $value = null, $subfield = null) {
        $subfields = ($subfield === null) ? array('a', '0') : array($subfield, '0');
        $fields = $this->marcRecord->getFields($field);

        foreach ($fields as $f) {
            $subfieldValues = $this->getSubfieldArray($f, $subfields, false);
            if (($value == null) || (isset($subfieldValues[0]) && ($subfieldValues[0] == $value))) {
                return isset($subfieldValues[1]) ? $subfieldValues[1] : null;
            }
        }

        return null;
    }

    /**
     * Tries to find the values and the authorities for the specified MARC field.
     * If no subfield is given, the first subfield (a) is used instead.
     *
     * @param string      $field    The MARC field
     * @param string|null $subfield The MARC subfield
     *
     * @return array|null An array with the 'value' and the 'authority'
     */
    public function getAllAuthorityForField($field, $subfield = null) {
        $results = array();
        $fields = $this->marcRecord->getFields($field);
        $subfields = ($subfield === null) ? array('a', '0') : array($subfield, '0');

        foreach ($fields as $f) {
            $subfieldValues = $this->getSubfieldArray($f, $subfields, false);
            $value = (isset($subfieldValues[0])) ? $subfieldValues[0] : null;
            $authority = (isset($subfieldValues[1])) ? $subfieldValues[1] : null;
            $results[] = array('value' => $value, 'authority' => $authority);
        }

        return $results;
    }

    /**
     * Determine if this is the journal known as the NEHA.
     *
     * @return bool True if this is the journal known as the NEHA.
     */
    public function isIRSH() {
        $u = $this->getFirstFieldValue('856', array('u'));

        return (preg_match('/^http:\/\/hdl\.handle\.net\/10622\/\d{8}-\d{4}-\d{3}$/', $u) == 1);
    }

    /**
     * Groups all author names and links under the same MARC type:
     * [type1: [[name: author1, link: author1], [name: author2, link: author2], [name: author3, link: author3]],
     *  type2: [[name: author4, link: author4], [name: author5, link: author5]]]
     *
     * @return array All author names and links grouped under the same MARC type.
     */
    public function getAuthorship() {
        $authors = array();
        foreach (array(100, 110, 111, 700, 710, 711) as $tag) {
            $key = 'author' . $tag;
            $fields = $this->marcRecord->getFields($tag);
            foreach ($fields as $field) {
                $subfields = $field->getSubfields();

                $link = '';
                $name = '';
                $role = $key;

                foreach ($subfields as $subfield) {
                    switch ($subfield->getCode()) {
                        case 'a':
                            $link = $subfield->getData();
                        case 'b':
                        case 'c':
                        case 'd':
                            $name = $name . $subfield->getData() . ' ';
                            break;
                        case 'e':
                            $role = $this->normalize($subfield->getData());
                            break;
                    }
                }

                if ($name) {
                    $item = array('name' => $this->normalize($name), 'link' => $link);
                    if (isset($authors[$role])) {
                        array_push($authors[$role], $item);
                    }
                    else {
                        $authors[$role] = array($item);
                    }
                }
            }
        }

        return $authors;
    }

    /**
     * Returns the PID that refers to this record in Search.
     *
     * @return string|null The PID that refers to this record in Search.
     */
    public function getIsShownAt() {
        return $this->getFirstFieldValue('902', array('a'));
    }

    /**
     * Returns the PID that refers to digital material belonging to this record.
     *
     * @return string|null The PID that refers to digital material belonging to this record.
     */
    public function getIsShownBy() {
        $p = $this->getFirstFieldValue('852', array('p'));
        $pos = strpos($p, '30051');
        if ($pos === false) {
            $j = $this->getFirstFieldValue('852', array('j'));
            if ($j == "Embedded") {
                $u = $this->getFirstFieldValue('856', array('u'));
                if ($u) {
                    $pos = strpos($u, '/10622/');

                    return ($pos === false) ? null : substr($u, $pos + 7);
                }
            }
        }

        return ($pos === false) ? null : $p;
    }

    /**
     * Returns the copyright 'A' field.
     *
     * @return string The copyright 'a' field.
     */
    public function getCopyrightA() {
        return $this->getFirstFieldValue('540', array('a'));
    }

    /**
     * Returns the copyright 'B' field.
     *
     * @return string The copyright 'b' field.
     */
    public function getCopyrightB() {
        return $this->getFirstFieldValue('540', array('b'));
    }

    /**
     * Returns the journal.
     *
     * @return string|null The journal.
     */
    public function getJournal() {
        return $this->getFirstFieldValue('730', array('a'));
    }

    /**
     * Returns all the holdings.
     *
     * @return array All the holdings.
     */
    public function getHoldings() {
        $holdings = array();
        $i = 1;
        $key = null;
        $datafields = $this->marcRecord->getFields();

        foreach ($datafields as $datafield) {
            $tag = $datafield->getTag();

            if ($tag == '852') {
                $subfieldc = $datafield->getSubfield('c');
                $subfieldj = $datafield->getSubfield('j');
                $subfieldp = $datafield->getSubfield('p');
                $subfield = null;

                if ($subfieldc && $subfieldj) {
                    $subfield = $subfieldc->getData() . ' ' . $subfieldj->getData();
                }
                if ($subfieldc && !$subfieldj) {
                    $subfield = $subfieldc->getData();
                }
                if (!$subfieldc && $subfieldj) {
                    $subfield = $subfieldj->getData();
                }
                if ($subfield) {
                    $key = $i++;
                    $holdings[$key]['c'] = $subfield;
                    if ($subfieldj) {
                        $holdings[$key]['j'] = $subfieldj->getData();
                    }
                    if ($subfieldp) {
                        $holdings[$key]['p'] = $subfieldp->getData();
                    }
                }
            }

            if ($tag == '866' && $key) {
                $subfield = $datafield->getSubfield('a');
                if ($subfield) {
                    $holdings[$key]['note'] = $subfield->getData();
                }
            }
            else if ($tag == '866' && !$key) {
                print('Key was null '); // TODO: Print?
            }
        }

        return $holdings;
    }

    /**
     * Returns the classifications.
     *
     * @return array The classifications.
     */
    public function getClassifications() {
        $classifications = array();

        $marcClassifications = $this->marcRecord->getFields('690');
        if ($marcClassifications) {
            foreach ($marcClassifications as $classification) {
                // Is there an address in the current field?
                $code = $classification->getSubfield('a');
                if ($code) {
                    $english = $classification->getSubfield('b');
                    $dutch = $classification->getSubfield('c');

                    $classifications[] = array(
                        'code'    => $code->getData(),
                        'english' => $english ? $english->getData() : null,
                        'dutch'   => $dutch ? $dutch->getData() : null
                    );
                }
            }
        }

        return $classifications;
    }

    /**
     * Returns the article.
     *
     * @return string The article.
     */
    public function getArticle() {
        $a = $this->getFirstFieldValue('773', array('a'));
        $t = $this->getFirstFieldValue('773', array('t'));
        $g = $this->getFirstFieldValue('773', array('g'));

        if ($a && $g) {
            return $a . ', ' . $g;
        }
        if ($t && $g) {
            return $t . ', ' . $g;
        }
        if ($a) {
            return $a;
        }

        return ($t) ? $t : $g;
    }

    /**
     * Returns the extended date span publisher.
     *
     * @return string The extended date span publisher.
     */
    public function getExtendedDateSpanPublisher() {
        $e = $this->getFirstFieldValue('260', array('e'));
        $f = $this->getFirstFieldValue('260', array('f'));

        if ($e && $f) {
            return $e . $f;
        }

        return ($e) ? $e : $f;
    }

    /**
     * Returns the URL that resolves to the image resource.
     *
     * @return string|null The URL that resolves to the image resource.
     */
    public function getImageURL() {
        $pid = $this->getIsShownBy();
        if (!empty($pid)) {
            $url = 'https://hdl.handle.net/10622/' . $pid;

            switch ($this->getLargestPossibleSize()) {
                case 'large':
                    return $url;
                case 'small':
                    return $url . '?locatt=view:level3';
                case 'medium':
                default:
                    return $url . '?locatt=view:level2';
            }
        }

        return null;
    }

    /**
     * Determine the largest possible image size, based on the publication status.
     *
     * @param string $largestSize The largest possible size anyway.
     *
     * @return string The largest possible image size.
     */
    public function getLargestPossibleSize($largestSize = 'large') {
        // If access to the images is granted, the largest size is always available
        if ($this->iishNetwork->isInternal()) {
            return $largestSize;
        }

        switch ($this->getPublicationStatus()) {
            case 'minimal':
            case 'pictoright':
                return 'small';
            case 'closed':
            case 'restricted':
            default:
                return $largestSize;
        }
    }

    /**
     * Returns an OAI identifier reference.
     * Either the Solr ID or the value of the 902$a field.
     *
     * @return string An OAI identifier reference.
     */
    public function getOAIPid() {
        $pid = $this->getFirstFieldValue('902', array('a'));
        // TODO: Find a better way to determine the OAI identifier. Could introduce a 903$a.
        $id = (strlen($pid) === 42) ? $this->getUniqueID() : $pid;

        $oaiPrefix = isset($this->iishConfig->OAI->prefix)
            ? $this->iishConfig->OAI->prefix
            : 'oai:socialhistoryservices.org:';

        return $oaiPrefix . $id;
    }

    /**
     * Returns the main author role.
     *
     * @return string The main author role.
     */
    public function getMainAuthorRole() {
        return $this->getFirstFieldValue('100', array('e'));
    }

    /**
     * Get the publication dates of the record.
     * See also getDateSpan().
     *
     * Override with the 'original' MARC record value to prevent translations.
     * Example: return original [190?] value vs. 1900 as translated in the field 'publishDate'.
     *
     * @return array
     */
    public function getPublicationDates() {
        return $this->getFieldArray('260', array('c'));
    }

    /**
     * Return an array of associative URL arrays with one or more of the following
     * keys:
     *
     * <li>
     *   <ul>desc: URL description text to display (optional)</ul>
     *   <ul>url: fully-formed URL (required if 'route' is absent)</ul>
     *   <ul>route: VuFind route to build URL with (required if 'url' is absent)</ul>
     *   <ul>routeParams: Parameters for route (optional)</ul>
     *   <ul>queryString: Query params to append after building route (optional)</ul>
     * </li>
     *
     * @return array
     */
    public function getUrls() {
        $urls = parent::getUrls();

        if ($this->isIRSH()) {
            foreach ($urls as $key => $url) {
                $address = $url['url'];

                // Find out if the URL contains a query part, if so, replace it
                $pos = strpos($address, '?');
                if ($pos !== false) {
                    $address = substr_replace($address, '?locatt=view:master', $pos);
                }
                else {
                    $address = $address . '?locatt=view:master';
                }

                if ($url['url'] === $url['desc']) {
                    $urls[$key]['desc'] = $address;
                }
                $urls[$key]['url'] = $address;
            }
        }

        return $urls;
    }

    /**
     * Returns one of three things: a full URL to a thumbnail preview of the record
     * if an image is available in an external system; an array of parameters to
     * send to VuFind's internal cover generator if no fixed URL exists; or false
     * if no thumbnail can be generated.
     *
     * @param string $size Size of thumbnail (small, medium or large -- small is default).
     *
     * @return string|array|bool
     */
    public function getThumbnail($size = 'small') {
        $thumbnail = parent::getThumbnail($size);

        if ($pid = $this->getAudioVisualPid()) {
            $thumbnail = is_array($thumbnail) ? $thumbnail : array();

            $thumbnail['pid'] = $pid;
            $thumbnail['size'] = $this->getLargestPossibleSize($size);
            $thumbnail['publication'] = $this->getPublicationStatus();

            $formats = $this->getFormats();
            if ($this->getDownloadable() && (strtolower($formats[0]) === 'music and sound')) {
                $thumbnail['audio'] = 'audio';
            }
        }

        return $thumbnail;
    }

    /**
     * Whether this record driver also has text indexed.
     *
     * @return bool Whether this record driver also has text indexed.
     */
    public function hasTextIndexed() {
        $searchService = $this->serviceLocator->get('VuFind\Search');
        $highlighting = new \IISH\Search\Highlighting($searchService, $this);
        return $highlighting->hasTextIndexed();
    }

    /**
     * Extract the barcode or PID from the URLs for this record.
     *
     * @return bool|string The barcode or PID, if found.
     */
    private function getAudioVisualPid() {
        $urls = $this->getURLs();
        foreach ($urls as $url) {
            $pos = strpos($url['url'], '/10622/');
            if ($pos > 1) {
                $tmp = substr($url['url'], $pos + 7);
                $pos = strpos($tmp, '?');

                return ($pos) ? substr($tmp, 0, $pos) : $tmp;
            }
        }

        $barcodes = $this->getBarcodesWithManifest();
        if (isset($barcodes[0])) {
            return $barcodes[0];
        }

        return false;
    }

    /**
     * Normalize the given text:
     * - Trim the text.
     * - Remove the '.' or ',' at the end.
     * - Make sure the first character is uppercase.
     *
     * @param string|array $text The text to normalize.
     *
     * @return string|array The normalized text.
     */
    protected function normalize($text) {
        if (is_array($text)) {
            foreach ($text as $key => $value) {
                $text[$key] = self::normalize($value);
            }
            return $text;
        }

        $text = trim($text);
        $i = strlen($text) - 1;
        if (($i >= 0) && (($text[$i] === '.') || ($text[$i] === ','))) {
            $text = substr($text, 0, $i);
        }

        return ucfirst($text);
    }

    /**
     * Escape the text, if it ends with a single character, remove it. (Usually /)
     *
     * @param string $text The text to be escaped.
     *
     * @return string The text escaped.
     */
    protected function escape($text) {
        return preg_replace('/\s.\Z/', '', $text);
    }
}