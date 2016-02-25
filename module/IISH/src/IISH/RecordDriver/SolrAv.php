<?php
namespace IISH\RecordDriver;

/**
 * Model for audiovisual MARC records in Solr.
 *
 * @package IISH\RecordDriver
 */
class SolrAv extends SolrMarc {

    /**
     * Returns the subject person field.
     *
     * @return array The subject person field.
     */
    public function getSubjectPerson() {
        return self::normalize($this->getFieldArray('600', array('a')));
    }

    /**
     * Returns the subject corporation field.
     *
     * @return array The subject corporation field.
     */
    public function getSubjectCorporation() {
        return self::normalize($this->getFieldArray('610', array('a')));
    }

    /**
     * Returns the subject meeting field.
     *
     * @return array The subject meeting field.
     */
    public function getSubjectMeeting() {
        return self::normalize($this->getFieldArray('611', array('a', 'e', 'n', 'd', 'c')));
    }

    /**
     * Returns the subject field.
     *
     * @return array The subject field.
     */
    public function getSubject() {
        return self::normalize($this->getFieldArray('650', array('a')));
    }

    /**
     * Returns the subject location field.
     *
     * @return array The subject location field.
     */
    public function getSubjectLocation() {
        return self::normalize($this->getFieldArray('651', array('a')));
    }

    /**
     * Returns the barcode of the visual document.
     *
     * @return string The barcode of the visual document.
     */
    public function getBarcode() {
        return self::normalize($this->getFirstFieldValue('852', array('p')));
    }

    /**
     * Returns the period.
     *
     * @return array The period.
     */
    public function getPeriod() {
        return self::normalize($this->getFieldArray('648'));
    }

    /**
     * Returns the genres.
     *
     * @return array The genres.
     */
    public function getGenres() {
        $genre = isset($this->fields['genre']) ? $this->fields['genre'] : array();
        $retval = array();
        foreach ($genre as $g) {
            $retval[] = array($g);
        }

        return self::normalize($retval);
    }

    /**
     * Get all subject headings associated with this record.  Each heading is
     * returned as an array of chunks, increasing from least specific to most
     * specific.
     *
     * Override to exclude fields.
     *
     * @return array All subject headings.
     */
    public function getAllSubjectHeadings() {
        // These are the fields that may contain subject headings:
        $fields = array('600', '610', '630', '650', '651');

        // This is all the collected data:
        $retval = array();

        // Try each MARC field one at a time:
        foreach ($fields as $field) {
            // Do we have any results for the current field?  If not, try the next.
            $results = $this->marcRecord->getFields($field);
            if (!$results) {
                continue;
            }

            // If we got here, we found results -- let's loop through them.
            foreach ($results as $result) {
                // Start an array for holding the chunks of the current heading:
                $current = array();

                // Get all the chunks and collect them together:
                $subfields = $result->getSubfields();
                if ($subfields) {
                    foreach ($subfields as $subfield) {
                        // Numeric subfields are for control purposes and should not
                        // be displayed:
                        if (!is_numeric($subfield->getCode())) {
                            $current[] = $subfield->getData();
                        }
                    }
                    // If we found at least one chunk, add a heading to our result:
                    if (!empty($current)) {
                        $retval[] = $current;
                    }
                }
            }
        }

        // Send back everything we collected:
        return $retval;
    }
} 