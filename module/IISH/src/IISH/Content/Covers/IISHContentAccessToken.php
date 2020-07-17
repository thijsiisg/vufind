<?php
namespace IISH\Content\Covers;

use Zend\Config\Config;
use IISH\Content\IISHNetwork;

/**
 * Depending on the users network an access token may be returned to access closed images from the SOR.
 *
 * @package IISH\Content\Covers
 */
class IISHContentAccessToken {

    /**
     * @var string|null
     * The access token
     */
    private $accessToken;

    /**
     * @var IISHNetwork
     * The IISH network
     */
    private $iishNetwork;

    /**
     * Constructor.
     *
     * @param Config $iishConfig The IISH configuration.
     */
    public function __construct(Config $iishConfig) {
        $this->accessToken = isset($iishConfig->SOR->accessToken)
            ? $iishConfig->SOR->accessToken
            : null;

        $this->iishNetwork = new IISHNetwork($iishConfig);
    }

    /**
     * Returns the access token if the user may have access.
     * Otherwise null is returned.
     *
     * @return null|string The access token or null.
     */
    public function getAccessToken() {
        if ($this->iishNetwork->isInternal()) {
            return $this->accessToken;
        }

        return null;
    }
}