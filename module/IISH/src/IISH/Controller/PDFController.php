<?php
namespace IISH\Controller;

use IISH\PDF\Loader;
use VuFind\Controller\AbstractBase;

/**
 * Generates covers for book entries.
 *
 * @package IISH\Controller
 */
class PDFController extends AbstractBase
{


    /**
     * Cover loader
     *
     * @var Loader
     */
    protected $loader = false;


    /**
     * Get the pdf loader object.
     *
     * Override to make use of overridden pdf loader object instead.
     *
     * @return Loader PDF loader.
     */
    protected function getLoader()
    {
        // Construct object for loading pdf files if it does not already exist:
        if (!$this->loader) {
            $this->loader = new Loader(
                $this->getConfig(),
                $this->getServiceLocator()->get('VuFind\ContentCoversPluginManager'),
                $this->getServiceLocator()->get('VuFindTheme\ThemeInfo'),
                $this->getServiceLocator()->get('VuFind\Http')->createClient(),
                $this->getServiceLocator()->get('VuFind\CacheManager')->getCacheDir()
            );
            \VuFind\ServiceManager\Initializer::initInstance(
                $this->loader, $this->getServiceLocator()
            );
        }

        return $this->loader;
    }

    /**
     * Send pdf display
     *
     * @return \Zend\Http\Response
     */
    public function homeAction()
    {

        $this->getLoader()->loadFile($this->params()->fromQuery('id'));
        $response = $this->getResponse();
        $headers = $response->getHeaders();
        $headers->addHeaderLine(
            'Content-type', 'application/pdf'
        );

        // Send proper caching headers so that the user's browser
        // is able to cache the cover images and not have to re-request
        // then on each page load. Default TTL set at 14 days

        $ttl = $this->getLoader()->getTtl();
        $headers->addHeaderLine(
            'Cache-Control', "maxage=" . $ttl
        );
        $headers->addHeaderLine(
            'Pragma', 'public'
        );
        $headers->addHeaderLine(
            'Expires', gmdate('D, d M Y H:i:s', time() + $ttl) . ' GMT'
        );
        if ($this->getLoader()->getFile())
            $response->setContent($this->getLoader()->getFile());
        else
            $response->setStatusCode(404);
        return $response;
    }
}
