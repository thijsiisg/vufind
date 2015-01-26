<?php
namespace IISH\Controller;
use Zend\ServiceManager\ServiceManager;

/**
 * Factory for controllers.
 *
 * @package IISH\Controller
 */
class Factory {

    /**
     * Construct the RecordController.
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return RecordController
     */
    public static function getRecordController(ServiceManager $sm) {
        return new RecordController(
            $sm->getServiceLocator()->get('VuFind\Config')->get('config'),
            $sm->getServiceLocator()->get('VuFind\Config')->get('iish')
        );
    }

    /**
     * Construct the OrderController.
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return OrderController
     */
    public static function getOrderController(ServiceManager $sm) {
        return new OrderController(
            $sm->getServiceLocator()->get('VuFind\Config')->get('iish')->Order->toArray()
        );
    }
} 