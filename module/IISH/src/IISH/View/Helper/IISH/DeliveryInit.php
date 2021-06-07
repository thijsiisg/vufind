<?php
namespace IISH\View\Helper\IISH;
use Zend\Config\Config;
use Zend\I18n\View\Helper\AbstractTranslatorHelper;

/**
 * DeliveryInit helper for setting up the integration of Delivery.
 *
 * @package IISH\View\Helper\IISH
 */
class DeliveryInit extends AbstractTranslatorHelper {
    /**
     * @var string
     */
    private $deliveryUrl;

    /**
     * @param Config $iishConfig IISH configuration.
     */
    public function __construct(Config $iishConfig) {
        $this->deliveryUrl = isset($iishConfig->Delivery->url)
            ? $iishConfig->Delivery->url
            : 'delivery.socialhistory.org';
    }

    /**
     * Initializes the Delivery scripts.
     */
    public function scripts() {
        $this->view->jsobject()->addProps(array(
            'url'  => $this->deliveryUrl,
            'lang' => $this->view->layout()->userLang,
            'requestAccess' => $this->getTranslator()->translate('Request access'),
            'warningOnlineContent' => $this->getTranslator()->translate('Content available'),
            'reservationTooltip' => $this->getTranslator()->translate('ReservationTooltip'),
            'reproductionTooltip' => $this->getTranslator()->translate('ReproductionTooltip'),
			'permissionTooltip' => $this->getTranslator()->translate('PermissionTooltip'),
            'archiveInventoryMessage' => $this->getTranslator()->translate('ArchiveInventoryMessage'),
            'archiveNoInventoryMessage' => $this->getTranslator()->translate('ArchiveNoInventoryMessage'),
			'coronaMessage' => $this->getTranslator()->translate('CoronaMessage')
        ));

        $this->view->headScript()->appendScript($this->view->jsobject()->getScript('delivery'));
        $this->view->headScript()->appendFile('delivery.js');
        $this->view->headScript()->appendFile('delivery_shop/delivery.locale.nl.js');
        $this->view->headScript()->appendFile('delivery_shop/delivery.locale.en.js');
        $this->view->headScript()->appendFile('delivery_shop/delivery_shop.js');
    }

    /**
     * Returns the shopping cart HTML.
     *
     * @return string The shopping cart HTML.
     */
    public function shoppingCart() {
        return $this->view->render('shopping_cart.phtml');
    }
} 