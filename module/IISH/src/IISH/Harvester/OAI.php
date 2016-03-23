<?php
namespace IISH\Harvester;

use VuFind\Harvester\OAI as VuFindOAI;

/**
 * This class harvests records via OAI-PMH using settings from oai.ini.
 *
 * Override to make some changes in the harvesting behavior.
 *
 * @package IISH\Harvester
 */
class OAI extends VuFindOAI
{
    /**
     * Filename of the document that stored all records.
     *
     * @var string
     */
    private $catalog;

    /**
     * Marc validation schema location
     */
    private $schema;

    /**
     * Constructor.
     *
     * Override to add support for a catalog document.
     *
     * @param string $target Target directory for harvest.
     * @param array $settings OAI-PMH settings from oai.ini.
     * @param \Zend\Http\Client $client HTTP client
     * @param string $from Harvest start date (omit to use last_harvest.txt)
     * @param string $until Harvest end date (optional)
     */
    public function __construct($target, $settings, \Zend\Http\Client $client, $from = null, $until = null)
    {
        $client->setOptions(array('sslverifypeer' => false));
        parent::__construct($target, $settings, $client, $from, $until);
        $this->catalog = $this->basePath . 'catalog.xml';
        $this->schema = LOCAL_OVERRIDE_DIR . '/harvest/marc21slim_custom.xsd';
    }

    /**
     * Harvest all available documents.
     *
     * Override to add support for the catalog document.
     *
     * @return void
     */
    public function launch()
    {
        // Open the XML document
        file_put_contents($this->catalog,
            '<?xml version="1.0" encoding="UTF-8"?>' .
            '<marc:catalog xmlns:marc="http://www.loc.gov/MARC21/slim" ' .
            'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ' .
            'xsi:schemaLocation="http://www.loc.gov/MARC21/slim ' .
            'http://www.loc.gov/standards/marcxml/schema/MARC21slim.xsd">');

        parent::launch();

        // Close the XML document
        file_put_contents($this->catalog, '</marc:catalog>', FILE_APPEND);
    }

    /**
     * Make an OAI-PMH request.  Retry if there is an error; return a SimpleXML object on success.
     *
     * Override to continue in case of an error, rather than throwing an exception.
     *
     * @param string $verb OAI-PMH verb to execute.
     * @param array $params GET parameters for ListRecords method.
     *
     * @return object SimpleXML-formatted response.
     */
    protected function sendRequest($verb, $params = array())
    {
        // Debug:
        if ($this->verbose) {
            $this->write(
                "Sending request: verb = {$verb}, params = " . print_r($params, true)
            );
        }

        // Set up retry loop:
        while (true) {
            // Set up the request:
            $this->client->resetParameters();
            $this->client->setUri($this->baseURL);
            // TODO: make timeout configurable
            $this->client->setOptions(array('timeout' => 60));

            // Set authentication, if necessary:
            if ($this->httpUser && $this->httpPass) {
                $this->client->setAuth($this->httpUser, $this->httpPass);
            }

            // Load request parameters:
            $query = $this->client->getRequest()->getQuery();
            $query->set('verb', $verb);
            foreach ($params as $key => $value) {
                $query->set($key, $value);
            }

            // Perform request and retry on error:
            $result = $this->client->setMethod('GET')->send();
            if ($result->getStatusCode() == 503) {
                $delayHeader = $result->getHeaders()->get('Retry-After');
                $delay = is_object($delayHeader)
                    ? $delayHeader->getDeltaSeconds() : 0;
                if ($delay > 0) {
                    if ($this->verbose) {
                        $this->writeLine(
                            "Received 503 response; waiting {$delay} seconds..."
                        );
                    }
                    sleep($delay);
                }
            } else if (!$result->isSuccess()) {
                $this->writeLine('Error: ' . $result->getReasonPhrase());
                sleep(5);
                continue;
            } else {
                // If we didn't get an error, we can leave the retry loop:
                break;
            }
        }

        // If we got this far, there was no error -- send back response.
        return $this->processResponse($result->getBody());
    }

    /**
     * Process an OAI-PMH response into a SimpleXML object. Retry if an error is detected.
     *
     * Override to return the errors rather than throwing an exception.
     *
     * @param string $xml OAI-PMH response XML.
     *
     * @return object     SimpleXML-formatted response.
     */
    protected function processResponse($xml)
    {
        // Sanitize if necessary:
        if ($this->sanitize) {
            $xml = $this->sanitizeXML($xml);
        }

        // Parse the XML:
        $result = simplexml_load_string($xml);
        if (!$result) {
            $e = "Problem loading XML: {$xml}";

            return simplexml_load_string('<errors><error>' . htmlspecialchars($e) . '</error></errors>');
        }

        // Detect errors and retry if one is found:
        if ($result->error) {
            $attribs = $result->error->attributes();
            $e = "OAI-PMH error -- code: {$attribs['code']}, value: {$result->error}";
            if ($attribs['code'] == 'noRecordsMatch')
                return simplexml_load_string('<ListRecords><!-- noRecordsMatch --></ListRecords>');
            else
                return simplexml_load_string('<errors><error>' . htmlspecialchars($e) . '</error></errors>');
        }

        // If we got this far, we have a valid response:
        return $result;
    }


    /**
     * Delete a record.
     *
     * @param id
     * The Solr id or last part of the Solr id as "prefix/identifier"
     *
     * @return void
     */
    protected function saveDeletedRecord($id)
    {
        $id = explode('/', $id, 2); // either id="prefix/identifier" or "identifier"
        $id = (count($id) == 1) ? $id[0] : $id[1];

        $delete_by_id = "wget -O /dev/null \"http://localhost:8080/solr/biblio/update?stream.body=<delete><id>" . $id . "</id></delete>\"";
        echo shell_exec($delete_by_id);

        $delete_fulltext = "wget -O /dev/null \"http://localhost:8080/solr/fulltext/update?stream.body=<delete><query>record:" . $id . "</query></delete>\"";
        echo shell_exec($delete_fulltext);
    }

    /**
     * Save a record to disk.
     *
     * Override to validate the record and to save to catalog instead.
     *
     * @param string $id ID of record to save.
     * @param object $record Record to save (in SimpleXML format).
     *
     * @throws \Exception
     */
    protected function saveRecord($id, $record)
    {
        if (!isset($record->metadata)) {
            throw new \Exception("Unexpected missing record metadata.");
        }

        // Extract the actual metadata from inside the <metadata></metadata> tags;
        // there is probably a cleaner way to do this, but this simple method avoids
        // the complexity of dealing with namespaces in SimpleXML:
        $xml = trim($record->metadata->asXML());
        preg_match('/^<metadata([^\>]*)>/', $xml, $extractedNs);
        $xml = preg_replace('/(^<metadata[^\>]*>)|(<\/metadata>$)/m', '', $xml);

        $marc = new \DOMDocument();
        if ($marc->loadXML($xml)) {
            if (!$marc->schemaValidate($this->schema)) {
                print("XML not valid for " . $id . "\n");
                return;
            }
        } else {
            print("XML cannot be parsed for " . $id . "\n");
            return;
        }

        // If we are supposed to inject any values, do so now inside the first
        // tag of the file:
        $insert = '';
        if (!empty($this->injectId)) {
            $insert .= "<{$this->injectId}>" . htmlspecialchars($id) .
                "</{$this->injectId}>";
        }
        if (!empty($this->injectDate)) {
            $insert .= "<{$this->injectDate}>" .
                htmlspecialchars((string)$record->header->datestamp) .
                "</{$this->injectDate}>";
        }
        if (!empty($this->injectSetSpec)) {
            if (isset($record->header->setSpec)) {
                foreach ($record->header->setSpec as $current) {
                    $insert .= "<{$this->injectSetSpec}>" .
                        htmlspecialchars((string)$current) .
                        "</{$this->injectSetSpec}>";
                }
            }
        }
        if (!empty($this->injectSetName)) {
            if (isset($record->header->setSpec)) {
                foreach ($record->header->setSpec as $current) {
                    $name = $this->setNames[(string)$current];
                    $insert .= "<{$this->injectSetName}>" .
                        htmlspecialchars($name) .
                        "</{$this->injectSetName}>";
                }
            }
        }
        if (!empty($this->injectHeaderElements)) {
            foreach ($this->injectHeaderElements as $element) {
                if (isset($record->header->$element)) {
                    $insert .= $record->header->$element->asXML();
                }
            }
        }
        if (!empty($insert)) {
            $xml = preg_replace('/>/', '>' . $insert, $xml, 1);
        }
        $xml = $this->fixNamespaces(
            $xml, $record->getDocNamespaces(),
            isset($extractedNs[1]) ? $extractedNs[1] : ''
        );

        // Save our XML:
        file_put_contents($this->catalog, trim($xml) . "\n", FILE_APPEND);
    }

    /**
     * Save harvested records to disk and track the end date.
     *
     * Override to use deleteRecord rather than saveDeletedRecord.
     * And always store harvested ids, even if the record is deleted.
     *
     * @param object $records SimpleXML records.
     *
     * @throws \Exception
     */
    protected function processRecords($records)
    {
        $this->writeLine('Processing ' . count($records) . " records...");

        // Array for tracking successfully harvested IDs:
        $harvestedIds = array();

        // Loop through the records:
        foreach ($records as $record) {
            // Die if the record is missing its header:
            if (empty($record->header)) {
                throw new \Exception("Unexpected missing record header.");
            }

            // Get the ID of the current record:
            $id = $this->extractID($record);

            // Save the current record, either as a deleted or as a regular file:
            $attribs = $record->header->attributes();
            if (strtolower($attribs['status']) == 'deleted') {
                $this->saveDeletedRecord($id);
            } else {
                $this->saveRecord($id, $record);
            }
            $harvestedIds[] = $id;

            // If the current record's date is newer than the previous end date,
            // remember it for future reference:
            $date = $this->normalizeDate($record->header->datestamp);
            if ($date && $date > $this->endDate) {
                $this->endDate = $date;
            }
        }

        // Do we have IDs to log and a log filename?  If so, log them:
        if (!empty($this->harvestedIdLog) && !empty($harvestedIds)) {
            $file = fopen($this->basePath . $this->harvestedIdLog, 'a');
            if (!$file) {
                throw new \Exception("Problem opening {$this->harvestedIdLog}.");
            }
            fputs($file, implode(PHP_EOL, $harvestedIds));
            fclose($file);
        }
    }
}