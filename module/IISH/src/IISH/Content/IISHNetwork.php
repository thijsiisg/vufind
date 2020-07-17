<?php
namespace IISH\Content;

use Zend\Config\Config;
use Zend\View\Helper\AbstractHelper;

/**
 * Determine access based on the users network.
 *
 * @package IISH\Content
 */
class IISHNetwork extends AbstractHelper {

    /**
     * @var array
     * Header keys of forwarded values
     */
    private static $X_FORWARD_HEADERS = array(
        'X-FORWARDED-FOR',
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR'
    );

    /**
     * @var string[]
     * A List of IP addresses and ranges.
     */
    private $audienceInternal;

    /**
     * Constructor.
     *
     * @param Config $iishConfig The IISH configuration.
     */
    public function __construct(Config $iishConfig) {
        $this->audienceInternal = isset($iishConfig->SOR->audienceInternal)
            ? explode(',', $iishConfig->SOR->audienceInternal)
            : array();
    }

    /**
     * Determine whether the user is inside the IISH network based on the users IP address.
     *
     * @return bool Whether the user is inside the IISH network.
     */
    public function isInternal() {
        $clientIP = self::getClientIP();
        foreach ($this->audienceInternal as $network) {
            if (self::checkNetwork($network, $clientIP) === true) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns the forwarded IP address value.
     *
     * @return string|null of an IP address.
     */
    private static function getClientIP() {
        foreach (self::$X_FORWARD_HEADERS as $header) {
            if (isset($_SERVER[$header])) {
                $ip_list = explode(',', $_SERVER[$header]);
                if (count($ip_list) > 0)
                    return trim($ip_list[0]);
            }
        }
        return null;
    }

    /**
     * Check if the IP is part of the given network.
     *
     * @param string $network The network.
     * @param string $ip The IP address.
     *
     * @return bool Whether the IP is part of the given network.
     */
    private static function checkNetwork($network, $ip) {
        $network = trim($network);

        if ($ip === $network) {
            return true;
        }

        $network = str_replace(' ', '', $network);
        if (strpos($network, '*') !== false) {
            if (strpos($network, '/') !== false) {
                $asParts = explode('/', $network);
                $network = @$asParts[0];
            }

            $nCount = substr_count($network, '*');
            $network = str_replace('*', '0', $network);
            if ($nCount === 1) {
                $network .= '/24';
            } else if ($nCount === 2) {
                $network .= '/16';
            } else if ($nCount === 3) {
                $network .= '/8';
            } else if ($nCount > 3) {
                return true; // if *.*.*.*, then all, so matched
            }
        }

        $d = strpos($network, '-');
        if ($d === false) {
            $ip_arr = explode('/', $network);
            if (count($ip_arr) === 1) {
                return false;
            }
            if (!preg_match('@\d*\.\d*\.\d*\.\d*@', $ip_arr[0], $matches)) {
                $ip_arr[0] .= '.0'; // Alternate form 194.1.4/24
            }

            $network_long = ip2long($ip_arr[0]);
            $x = ip2long($ip_arr[1]);
            $mask = long2ip($x) === $ip_arr[1] ? $x : (0xffffffff << (32 - $ip_arr[1]));
            $ip_long = ip2long($ip);

            return (($ip_long & $mask) === ($network_long & $mask));
        } else {
            $from = trim(ip2long(substr($network, 0, $d)));
            $to = trim(ip2long(substr($network, $d + 1)));
            $ip_long = ip2long($ip);

            return ($ip_long >= $from && $ip_long <= $to);
        }
    }
}