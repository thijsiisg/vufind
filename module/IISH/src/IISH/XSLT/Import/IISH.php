<?php
namespace IISH\XSLT\Import;
use VuFind\XSLT\Import\VuFind;

/**
 * XSLT support class -- all methods of this class must be public and static;
 * they will be automatically made available to your XSL stylesheet for use
 * with the php:function() function.
 *
 * Extended the VuFind methods with extra methods necessary for the IISH modules.
 *
 * @package IISH\XSLT\Import
 */
class IISH extends VuFind {
    /**
     * @var \Zend\I18n\Translator\Translator
     */
    private static $translator;

    /**
     * Generates an ID.
     *
     * @param string       $key The key.
     * @param array|string $tag The tag.
     *
     * @return string The ID.
     */
    public static function generateID($key, $tag) {
        $t = (is_array($tag) && isset($tag[0])) ? $tag[0]->nodeValue : '';

        return 'A' . substr(md5($key . ':' . $t . str_repeat(' ', 10)), 0, 10);
    }

    /**
     * Truncates the given text with the given limit.
     *
     * @param string $text  The given text.
     * @param int    $limit The maximum limit.
     *
     * @return string The truncated text.
     */
    public static function truncate($text, $limit = 300) {
        $separators = ' -:;,';
        $length = strlen($text);

        $limit = intval($limit);
        if ($length < $limit) {
            return $text;
        }

        for ($i = $limit; $i < $length; $i++) {
            if (strrpos($separators, $text[$i]) !== false) {
                return substr($text, 0, $i);
            }
        }

        return $text;
    }

    /**
     * Translates the given key.
     *
     * @param string $key The key.
     *
     * @return string The translation (if found).
     */
    public static function translate($key) {
        return self::getTranslator()->translate($key);
    }

    /**
     * The translator interface.
     *
     * @return \Zend\I18n\Translator\Translator
     */
    private static function getTranslator() {
        if (self::$translator === null) {
            self::$translator = self::$serviceLocator->get('VuFind\Translator');
        }

        return self::$translator;
    }
} 