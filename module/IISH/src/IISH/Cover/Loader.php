<?php
namespace IISH\Cover;
use abeautifulsite\SimpleImage;
use IISH\Content\AccessClosedCover;
use IISH\Content\ResizableCover;
use VuFind\Cover\Loader as VuFindCoverLoader;

/**
 * Book Cover Generator.
 *
 * @package IISH\Cover
 */
class Loader extends VuFindCoverLoader {
    /**
     * @var int
     */
    private $pid;

    /**
     * @var string
     */
    private $publication;

    /**
     * @var string
     */
    private $audio;

    /**
     * @var int
     */
    private $resizeToWidth;

    /**
     * Load an image given an ISBN and/or content type.
     *
     * Additional method to add support for PIDs and publication status.
     *
     * @param string $isbn        ISBN.
     * @param string $size        Requested size.
     * @param string $type        Content type.
     * @param string $title       Title of book (for dynamic covers).
     * @param string $author      Author of the book (for dynamic covers).
     * @param string $callnumber  Callnumber (unique id for dynamic covers).
     * @param string $issn        ISSN.
     * @param string $oclc        OCLC number.
     * @param string $upc         UPC number.
     * @param string $pid         PID.
     * @param string $publication Publication status.
     * @param string $audio       Audio.
     */
    public function loadImage($isbn = null, $size = 'small', $type = null,
                              $title = null, $author = null, $callnumber = null, $issn = null,
                              $oclc = null, $upc = null, $pid = null, $publication = null, $audio = null) {
        $this->pid = $pid;
        $this->publication = $publication;
        $this->audio = $audio;

        parent::loadImage($isbn, $size, $type, $title, $author, $callnumber, $issn, $oclc, $upc);
    }

    /**
     * Load the user-specified "cover unavailable" graphic (or default if none
     * specified).
     *
     * Override to add temporarily support for audio thumbnails if there is audio digitally available.
     *
     * @return void
     */
    public function loadUnavailable() {
        if ($this->audio === 'audio') {
            $this->contentType = 'image/png';
            $this->image = file_get_contents($this->searchTheme('images/audio.png'));
        }
        else {
            parent::loadUnavailable();
        }
    }

    /**
     * Support method for fetchFromAPI() -- set the localFile property.
     *
     * Override to add support for PIDs.
     *
     * @param array $ids IDs returned by getIdentifiers() method.
     *
     * @return string|void The local file path.
     */
    protected function determineLocalFile($ids) {
        if (isset($ids['pid'])) {
            return $this->getCachePath($this->size, 'PID' . $ids['pid']);
        }
        else {
            return parent::determineLocalFile($ids);
        }
    }

    /**
     * Get all valid identifiers as an associative array.
     *
     * Override to add support for PIDs.
     *
     * @return array
     */
    protected function getIdentifiers() {
        $ids = parent::getIdentifiers();
        if ($this->pid && strlen($this->pid) > 0) {
            $ids['pid'] = $this->pid;
        }

        return $ids;
    }

    /**
     * Load bookcover from cache or remote provider and display if possible.
     *
     * Override to add support for resizable cover images and access closed image.
     *
     * @return bool True if image loaded, false on failure.
     */
    protected function fetchFromAPI() {
        // Check that we have at least one valid identifier:
        $ids = $this->getIdentifiers();
        if (empty($ids)) {
            return false;
        }

        // Set up local file path:
        $this->localFile = $this->determineLocalFile($ids);
        if (is_readable($this->localFile)) {
            // Load local cache if available
            $this->contentType = 'image/jpeg';
            $this->image = file_get_contents($this->localFile);

            return true;
        }
        else if (isset($this->config->Content->coverimages)) {
            $providers = explode(',', $this->config->Content->coverimages);
            foreach ($providers as $provider) {
                $provider = explode(':', trim($provider));
                $apiName = strtolower(trim($provider[0]));
                $key = isset($provider[1]) ? trim($provider[1]) : null;
                try {
                    $handler = $this->apiManager->get($apiName);

                    // Is the current provider appropriate for the available data?
                    if ($handler->supports($ids)) {
                        if ($url = $handler->getUrl($key, $this->size, $ids)) {
                            // First check if access is closed
                            if (($handler instanceof AccessClosedCover) &&
                                $handler->isAccessClosed($key, $this->size, $ids, $this->publication)) {

                                $this->dieWithAccessClosedImage();
                                return true;
                            }

                            // See if we have to resize the cover image
                            $this->resizeToWidth = null;
                            if ($handler instanceof ResizableCover) {
                                $this->resizeToWidth = $handler->resizeToWidthFor($key, $this->size, $ids);
                            }

                            $success = $this->processImageURLForSource(
                                $url, $handler->isCacheAllowed(), $apiName
                            );
                            if ($success) {
                                return true;
                            }
                        }
                    }
                }
                catch (\Exception $e) {
                    $this->debug(
                        get_class($e) . ' during processing of ' . $apiName
                        . ': ' . $e->getMessage()
                    );
                }
            }
        }

        return false;
    }

    /**
     * This method either moves the temporary file to its final location (true)
     * or detects an error and deletes it (false).
     *
     * Override to add the possibility to resize an image if necessary.
     *
     * @param string $image     Raw image data
     * @param string $tempFile  Temporary file
     * @param string $finalFile Final file location
     *
     * @return bool
     */
    protected function validateAndMoveTempFile($image, $tempFile, $finalFile) {
        list($width, $height, $type) = @getimagesize($tempFile);

        // File too small -- delete it and report failure.
        if ($width < 2 && $height < 2) {
            @unlink($tempFile);

            return false;
        }

        // Image should resize to new maximum width.
        $this->resizeImage($tempFile);

        // Conversion needed -- do some normalization for non-JPEG images:
        if ($type != IMAGETYPE_JPEG) {
            // We no longer need the temp file:
            @unlink($tempFile);

            return $this->convertNonJpeg($image, $finalFile);
        }

        // If $tempFile is already a JPEG, let's store it in the cache.
        return @rename($tempFile, $finalFile);
    }

    /**
     * Display the default "access closed" graphic and terminate execution.
     */
    private function dieWithAccessClosedImage() {
        $this->contentType = 'image/png';
        $this->image = file_get_contents($this->searchTheme('images/no_access.png'));
    }

    /**
     * Resize an image to a maximum width, if a width is set.
     *
     * @param string $file The image file.
     */
    private function resizeImage($file) {
        if (is_int($this->resizeToWidth) && ($this->resizeToWidth > 0)) {
            $simpleImage = new SimpleImage();
            $simpleImage
                ->load($file)
                ->fit_to_width($this->resizeToWidth)
                ->save();
        }
    }
} 