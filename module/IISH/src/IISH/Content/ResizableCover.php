<?php
namespace IISH\Content;

/**
 * Used by cover content loaders that wish to have a resizable cover image.
 *
 * @package IISH\Content
 */
interface ResizableCover {

    /**
     * Cover image should resize to width returned.
     *
     * @param string $key  The API key.
     * @param string $size Size of image to load (small/medium/large).
     * @param array  $ids  Associative array of identifiers (keys may include 'isbn'
     *                     pointing to an ISBN object, 'issn' pointing to a string and 'oclc' pointing
     *                     to an OCLC number string).
     *
     * @return int|null The width to resize to.
     */
    public function resizeToWidthFor($key, $size, $ids);
}