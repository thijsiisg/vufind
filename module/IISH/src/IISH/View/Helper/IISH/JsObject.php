<?php
namespace IISH\View\Helper\IISH;
use Zend\View\Helper\AbstractHelper;

/**
 * JsObject helper for passing an associative array as an object to JavaScript.
 *
 * @package IISH\View\Helper\IISH
 */
class JsObject extends AbstractHelper {
    /**
     * @var array
     */
    private $properties;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->properties = array();
    }

    /**
     * Adds a property to the list of properties.
     *
     * @param mixed $name The name of the property.
     * @param mixed $value The value of the property.
     */
    public function addProp($name, $value) {
        $this->properties[$name] = $value;
    }

    /**
     * Adds a list of properties.
     *
     * @param array $properties A list of properties to add to the list.
     *                          Key = The name of the property.
     *                          Value = The value of the property.
     */
    public function addProps(array $properties) {
        $this->properties = array_merge($this->properties, $properties);
    }

    /**
     * Generates a JavaScript object with the list of properties.
     *
     * @param string $variableName The name of the variable in JavaScript that holds the object.
     *
     * @return string The JavaScript initializing the object consisting of the given properties.
     */
    public function getScript($variableName) {
        // Generate a JavaScript object through the json_encode function
        $jsObject = json_encode($this->properties, JSON_FORCE_OBJECT);

        // Empty the properties in memory for the next time
        $this->properties = array();

        return "var $variableName = $jsObject;";
    }
}