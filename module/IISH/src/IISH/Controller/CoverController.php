<?php
namespace IISH\Controller;
use IISH\Cover\Loader;
use VuFind\Controller\CoverController as VuFindCoverController;

/**
 * Generates covers for book entries.
 *
 * @package IISH\Controller
 */
class CoverController extends VuFindCoverController {

    /**
     * Get the cover loader object.
     *
     * Override to make use of overridden cover loader object instead.
     *
     * @return Loader Cover loader.
     */
    protected function getLoader() {
        // Construct object for loading cover images if it does not already exist:
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
     * Send image data for display in the view.
     *
     * Override to add support for PIDs and publication parameters.
     *
     * @return \Zend\Http\Response
     */
    public function showAction() {
        $this->writeSession(); // avoid session write timing bug
        $this->getLoader()->loadImage(
        // Legacy support for "isn" param which has been superseded by isbn:
            $this->params()->fromQuery('isbn', $this->params()->fromQuery('isn')),
            $this->params()->fromQuery('size'),
            $this->params()->fromQuery('contenttype'),
            $this->params()->fromQuery('title'),
            $this->params()->fromQuery('author'),
            $this->params()->fromQuery('callnumber'),
            $this->params()->fromQuery('issn'),
            $this->params()->fromQuery('oclc'),
            $this->params()->fromQuery('upc'),
            $this->params()->fromQuery('pid'),
            $this->params()->fromQuery('publication'),
            $this->params()->fromQuery('audio')
        );

        return $this->displayImage();
    }
}

