<?php
namespace IISH\View\Helper\IISH;
use IISH\Content\IISHNetwork;
use Zend\ServiceManager\ServiceManager;

/**
 * Factory for IISH view helpers.
 *
 * @package IISH\View\Helper\IISH
 */
class Factory {

    /**
     * Construct the DeliveryInit helper.
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return DeliveryInit
     */
    public static function getDeliveryInit(ServiceManager $sm) {
        $iishConfig = $sm->getServiceLocator()->get('VuFind\Config')->get('iish');

        return new DeliveryInit($iishConfig);
    }

    /**
     * Construct the SearchBox helper.
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return SearchBox
     */
    public static function getSearchBox(ServiceManager $sm) {
        $config = $sm->getServiceLocator()->get('VuFind\Config');
        return new SearchBox(
            $sm->getServiceLocator()->get('VuFind\SearchOptionsPluginManager'),
            $config->get('searchbox')->toArray()
        );
    }

	/**
	 * Construct the IISH Network helper.
	 *
	 * @param ServiceManager $sm Service manager.
	 *
	 * @return IISHNetwork
	 */
	public static function getIISHNetwork(ServiceManager $sm) {
		$iishConfig = $sm->getServiceLocator()->get('VuFind\Config')->get('iish');

		return new IISHNetwork($iishConfig);
	}
}
