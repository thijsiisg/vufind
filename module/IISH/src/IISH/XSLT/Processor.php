<?php
namespace IISH\XSLT;
use DOMDocument;
use XSLTProcessor;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * XSLT processing helper class.
 *
 * @package IISH\XSLT
 */
class Processor {
    /**
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    private $serviceLocator;

    /**
     * @var \DOMDocument
     */
    private $document;

    /**
     * @var string
     */
    private $filename;

    /**
     * @var array
     */
    private $params;

    /**
     * Constructor.
     * Helps processing XML documents against an XSL document.
     *
     * @param ServiceLocatorInterface $serviceLocator The service locator interface.
     * @param DOMDocument             $document       The document to be processed.
     * @param string                  $filename       Filename of the stylesheet.
     * @param array                   $params         Associative array of XSLT parameters.
     *
     * @see VuFind\XSLT\Processor
     */
    public function __construct(ServiceLocatorInterface $serviceLocator, DOMDocument $document,
                                $filename, array $params = array()) {
        $this->serviceLocator = $serviceLocator;
        $this->document = $document;
        $this->filename = $filename;
        $this->params = $params;
    }

    /**
     * Perform an XSLT transformation and return the results.
     *
     * @return string The result of the transformation as a string.
     */
    public function process() {
        $style = new DOMDocument();
        $style->load(APPLICATION_PATH . '/module/IISH/xsl/' . $this->filename);

        $xsl = new XSLTProcessor();
        $xsl->importStyleSheet($style);

        foreach ($this->params as $key => $value) {
            $xsl->setParameter('', $key, $value);
        }

        $class = 'IISH\XSLT\Import\IISH';
        $class::setServiceLocator($this->serviceLocator);

        $methods = get_class_methods($class);
        foreach ($methods as $method) {
            $xsl->registerPHPFunctions($class . '::' . $method);
        }

        return $xsl->transformToXML($this->document);
    }
} 