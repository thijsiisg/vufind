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
class OAI extends VuFindOAI {

    /**
     * Get the filename for a specific record ID.
     *
     * Override to add sub folders.
     *
     * @param string $id  ID of record to save.
     * @param string $ext File extension to use.
     *
     * @return string     Full path + filename.
     *
     * @throws \Exception
     */
    protected function getFilename($id, $ext) {
        // But we add a sub folder, because we don't want to overburden our fs now, do we?
        $f = $this->basePath . date('YmdHi') . '/';
        if (!is_dir($f) && !mkdir($f, true)) {
            throw new \Exception("Problem creating directory {$f}.");
        }

        return $f . time() . '_' . preg_replace('/[^\w]/', '_', $id) . '.' . $ext;
    }

    /**
     * Save a record to disk.
     *
     * Override to deal with missing metadata and updates access and modification time of the saved record.
     *
     * @param string $id        ID of record to save.
     * @param object $record    Record to save (in SimpleXML format).
     * @param string $extension The extension of the saved record.
     *
     * @throws \Exception
     */
    protected function saveRecord($id, $record, $extension = 'xml') {
        if (!isset($record->metadata)) {
            if (!isset($record->header)) {
                throw new \Exception('Unexpected missing record metadata and header.');
            }

            $xml = '<marc:record xmlns:marc="http://www.loc.gov/MARC21/slim"><marc:controlfield tag="001">' . $id .
                '</marc:controlfield><marc:datafield ind1=" " ind2=" " tag="902"><marc:subfield code="a">' . $id .
                '</marc:subfield></marc:datafield></marc:record>';
        }
        else {
            // Extract the actual metadata from inside the <metadata></metadata> tags;
            // there is probably a cleaner way to do this, but this simple method avoids
            // the complexity of dealing with namespaces in SimpleXML:
            $xml = trim($record->metadata->asXML());
            preg_match('/^<metadata([^\>]*)>/', $xml, $extractedNs);
            $xml = preg_replace('/(^<metadata[^\>]*>)|(<\/metadata>$)/m', '', $xml);
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
        $filename = $this->getFilename($id, $extension);
        file_put_contents($filename, trim($xml));

        $datestamp = (isset($record->header) && strlen((string)$record->header->datestamp) >= 10)
            ? strtotime(substr((string)$record->header->datestamp, 0, 10))
            : time();
        touch($filename, $datestamp);
    }

    /**
     * Save harvested records to disk and track the end date.
     *
     * Override to NOT save as a deleted record, but save it as a regular file.
     *
     * @param object $records SimpleXML records.
     *
     * @throws \Exception
     */
    protected function processRecords($records) {
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
                $this->saveRecord($id, $record);
            }
            else {
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