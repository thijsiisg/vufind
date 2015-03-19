<?php
namespace IISH\Controller;

use IISH\File\Loader;
use VuFind\Controller\AbstractBase;

/**
 * Act as s FileHandler.
 *
 * @package IISH\Controller
 */
class FileController extends AbstractBase
{


    /**
     * Cover loader
     *
     * @var Loader
     */
    protected $loader = false;


    /**
     * Get the file loader object.
     *
     * Override to get a new loader object instead.
     *
     * @return loader.
     */
    protected function getLoader()
    {
        // Construct object if it does not already exist:
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
     * Send file
     *
     * @return \Zend\Http\Response
     */
    public function homeAction()
    {
        $this->writeSession(); // avoid session write timing bug

        $this->getLoader()->setFilename($this->params()->fromQuery('filename'));
        $response = $this->getResponse();

        if ($this->getLoader()->hasFile()) {
            $contentType = $this->params()->fromQuery('contentType', 'application/octet-stream');
            $headers = $response->getHeaders();
            $headers->addHeaderLine(
                'Content-type', $contentType
            );

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

            $response->setContent($this->getLoader()->getFile());
        } else
            $response->setStatusCode(404);

        return $response;
    }
}
