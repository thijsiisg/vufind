<?php
namespace IISH\Content\Covers;
use IISH\Content\ResizableCover;
use VuFind\Content\AbstractCover;

/**
 * IISH cover content loader.
 *
 * @package IISH\Content\Covers
 */
class IISH extends AbstractCover implements ResizableCover {
    /**
     * @var string
     */
    private $accessToken;

    /**
     * Constructor.
     *
     * @param string $accessToken The access token for loading certain images.
     */
    public function __construct($accessToken) {
        $this->accessToken = $accessToken;
    }

    /**
     * Retrieve an audio\visual from the IISH.
     * The interpretation is the handle: http://hdl.handle.net/10622/[pid]?locatt=view":[size]
     * TODO: We append an access token if the request comes from a known able network that is 'ours'.
     *
     * @param string $key  The API key.
     * @param string $size Size of image to load (small/medium/large).
     * @param array  $ids  Associative array of identifiers (keys may include 'isbn'
     *                     pointing to an ISBN object, 'issn' pointing to a string and 'oclc' pointing
     *                     to an OCLC number string).
     *
     * @return string|bool The URL, or false if no URL can be obtained.
     */
    public function getUrl($key, $size, $ids) {
        if (!isset($ids['pid'])) {
            return false;
        }

        switch ($size) {
            case 'small':
                $imageIndex = 'level3';
                break;
            case 'large':
                $imageIndex = 'level2';
                break;
            case 'medium':
            default :
                $imageIndex = 'level2';
                break;
        }

        $accessTokenURL = ''; // TODO: When to give access token...
        if (!empty($this->accessToken)) {
            // TODO: $accessTokenURL = '&urlappend=?access_token=' . $this->accessToken;
        }

        return 'http://hdl.handle.net/10622/' . $ids['pid'] . '?locatt=view:' . $imageIndex . $accessTokenURL;
    }

    /**
     * Does this plugin support the provided ID array?
     *
     * Override to add support for PIDs.
     *
     * @param array $ids IDs that will later be sent to getUrl().
     *
     * @return bool Whether this cover content loader supports one
     */
    public function supports($ids) {
        return (parent::supports($ids) || isset($ids['pid']));
    }

    /**
     * Cover image should resize to width returned.
     * The images from this domain are a little bit too large, so we resize them to get a uniform width.
     *
     * @param string $key  The API key.
     * @param string $size Size of image to load (small/medium/large).
     * @param array  $ids  Associative array of identifiers (keys may include 'isbn'
     *                     pointing to an ISBN object, 'issn' pointing to a string and 'oclc' pointing
     *                     to an OCLC number string).
     *
     * @return int|null The width to resize to.
     */
    public function resizeToWidthFor($key, $size, $ids) {
        $reductionSize = null;
        if (($size !== 'small') && ($size !== 'large')) {
            $reductionSize = 350;
        }

        return $reductionSize;
    }
}