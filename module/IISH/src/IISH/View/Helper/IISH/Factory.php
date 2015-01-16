<?php
namespace IISH\View\Helper\IISH;
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
}
