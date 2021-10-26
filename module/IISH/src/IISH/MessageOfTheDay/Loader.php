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
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function __construct(ServiceLocatorInterface $serviceLocator) {
        parent::__construct($serviceLocator, 'MessageOfTheDay');

        $this->translator = $serviceLocator->get('VuFind\Translator');

        $iishConfig = $serviceLocator->get('VuFind\Config')->get('iish');
        $this->uri = 'http://socialhistory.org/en/services/maintenance';
        if (isset($iishConfig->MessageOfTheDay)) {
            $motdConfig = $iishConfig->MessageOfTheDay;
            $key = 'uri_' . $this->getLang();
            if (isset($motdConfig[$key])) {
                $this->uri = $motdConfig[$key];
            }
        }
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
			// Override message concerning corona virus
			if ($this->getLang() == 'nl') {
				return array(
					'title'   => 'Het IISG gebouw is beperkt geopend',
					'content' => 'Reserveer een plek in de studiezaal om de collecties te raadplegen > <a href="https://iisg.amsterdam/nl/collecties/gebruiken">Meer informatie</a>',
					'lang'    => $this->getLang()
				);
			}

			return array(
				'title'   => 'The IISH building is open on a limited basis',
				'content' => 'Book a reading room visit to view the collections > <a href="https://iisg.amsterdam/en/collections/using">More information</a>',
				'lang'    => $this->getLang()
			);

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
        $content = str_replace('.', '. ', $content);

        // fix broken url
	    $content = preg_replace("/([A-z\d_\-]{2,}\.)\s([A-z]{2,3}([^A-z\d]+.*)?)$/", '$1$2', $content);

        return array(
            'title'   => $title,
            'content' => $content,
            'lang'    => $lang
        );
    }
}
