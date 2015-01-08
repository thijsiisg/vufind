<?php
namespace IISH\OAI;
use \DOMDocument;
use IISH\Cache\Cacheable;
use Zend\Http\Request;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Retrieves records from the OAI.
 *
 * @package IISH\OAI
 */
class Loader extends Cacheable {
    /**
     * @var \VuFindHttp\HttpService
     */
    private $http;

    /**
     * @var \Zend\Config\Config
     */
    private $iishConfig;

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $pid;

    /**
     * @var string
     */
    private $metadataPrefix;

    /**
     * Constructor.
     * For obtaining records from the OAI.
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function __construct(ServiceLocatorInterface $serviceLocator) {
        parent::__construct($serviceLocator, 'OAI');

        $this->http = $serviceLocator->get('VuFind\Http');
        $this->iishConfig = $serviceLocator->get('VuFind\Config')->get('iish');
    }

    /**
     * The unique id of the record in Solr.
     *
     * @return string The id.
     */
    public function getId() {
        return $this->id;
    }

    /**
     * The unique id of the record in Solr.
     *
     * @param string $id The id.
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * The PID of the record to fetch.
     *
     * @return string The PID.
     */
    public function getPid() {
        return $this->pid;
    }

    /**
     * The PID of the record to fetch.
     *
     * @param string $pid The PID.
     */
    public function setPid($pid) {
        $this->pid = $pid;
    }

    /**
     * The format of the record to fetch as known to the OAI service.
     *
     * @return string The metadata prefix.
     */
    public function getMetadataPrefix() {
        return $this->metadataPrefix;
    }

    /**
     * The format of the record to fetch as known to the OAI service.
     *
     * @param string $metadataPrefix The metadata prefix.
     */
    public function setMetadataPrefix($metadataPrefix) {
        $this->metadataPrefix = $metadataPrefix;
    }

    /**
     * Returns the record, either from cache or from the OAI.
     *
     * @return DOMDocument The record.
     */
    public function getRecord() {
        $doc = new DOMDocument();
        $doc->loadXML($this->get());

        return $doc;
    }

    /**
     * The key of the cached OAI record.
     *
     * @return string The key.
     */
    protected function getKey() {
        return md5($this->getId() . '_' . $this->getMetadataPrefix());
    }

    /**
     * Loads the record from the OAI.
     *
     * @return string The record (XML) obtained from the OAI.
     *
     * @throws \IISH\Exception\OAI
     */
    protected function create() {
        $client = $this->http->createClient();
        $client->setOptions(array('sslverifypeer' => false));
        $client->setMethod(Request::METHOD_GET);
        $client->setUri($this->getOaiBaseUrl());
        $client->setParameterGet(array(
            'verb'           => 'GetRecord',
            'identifier'     => $this->getPid(), // The OAI identifier... not that of the Solr
            'metadataPrefix' => $this->getMetadataPrefix(),
        ));
        $response = $client->send();

        if ($response->isSuccess()) {
            return $response->getBody();
        }
        else {
            throw new \IISH\Exception\OAI('Record not found in OAI service: ' . $this->getPid());
        }
    }

    /**
     * Returns the base OAI URL.
     *
     * @return string The base OAI URL.
     */
    private function getOaiBaseUrl() {
        return isset($this->iishConfig->OAI->baseUrl)
            ? $this->iishConfig->OAI->baseUrl
            : 'http://api.socialhistoryservices.org/solr/all/oai';
    }
}