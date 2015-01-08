<?php
namespace IISH\MessageOfTheDay;
use IISH\Cache\Cacheable;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Creates a 'Message of the Day'.
 *
 * @package IISH\MessageOfTheDay
 */
class Loader extends Cacheable {
    /**
     * @var string
     */
    private $uri;

    /**
     * @var \Zend\I18n\Translator\Translator
     */
    private $translator;

    /**
     * Constructor.
     * For the creation of a 'message of the day'.
     *
     * @param ServiceLocatorInterface $serviceLocator.
     */
    public function __construct(ServiceLocatorInterface $serviceLocator) {
        parent::__construct($serviceLocator, 'MessageOfTheDay');

        $iishConfig = $serviceLocator->get('VuFind\Config')->get('iish');
        $this->uri = isset($iishConfig->MessageOfTheDay->uri)
            ? $iishConfig->MessageOfTheDay->uri
            : 'http://socialhistory.org/en/services/maintenance';

        $this->translator = $serviceLocator->get('VuFind\Translator');
    }

    /**
     * Retrieves from cache, or creates a new 'message of the day'.
     *
     * @return array|null The message of the day.
     */
    public function getMessageOfTheDay() {
        return $this->get();
    }

    /**
     * Creates a new 'message of the day' and caches it.
     *
     * @return array|null The message of the day.
     */
    protected function create() {
        try {
            $graph = \EasyRdf_Graph::newAndLoad($this->uri);

            $title = $graph->label();
            $content = $graph->get(
                $this->uri,
                'content:encoded',
                'literal',
                $this->getLang()
            );

            if ($content) {
                return $this->createMessage($title, $content, $this->getLang());
            }

            return null;
        }
        catch (\Exception $e) {
            return null;
        }
    }

    /**
     * The key of the cached item.
     *
     * @return string The key.
     */
    protected function getKey() {
        return 'motd_' . $this->getLang();
    }

    /**
     * The user selected language.
     *
     * @return string The locale.
     */
    private function getLang() {
        return is_object($this->translator) ? $this->translator->getLocale() : 'en';
    }

    /**
     * Creates a new array with all the data of the 'message of the day'.
     *
     * @param object|string $title   The title.
     * @param string        $content The content string.
     * @param string        $lang    The locale.
     *
     * @return array The message of the day.
     */
    private function createMessage($title, $content, $lang) {
        return array(
            'title'   => $title,
            'content' => str_replace('.', '. ', $content),
            'lang'    => $lang
        );
    }
}