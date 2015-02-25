<?php
namespace IISH\Content;

/**
 * Used by cover content loaders to determine whether the 'access closed' graphic should be shown.
 *
 * @package IISH\Content
 */
interface AccessClosedCover {

    /**
     * Determines whether access is closed for the given image.
     *
     * @param string      $key         The API key.
     * @param string      $size        Size of image to load (small/medium/large).
     * @param array       $ids         Associative array of identifiers (keys may include 'isbn'
     *                                 pointing to an ISBN object, 'issn' pointing to a string and 'oclc' pointing
     *                                 to an OCLC number string).
     * @param string|null $publication The publication parameter.
     *
     * @return bool Whether access is closed.
     */
    public function isAccessClosed($key, $size, $ids, $publication = null);
}