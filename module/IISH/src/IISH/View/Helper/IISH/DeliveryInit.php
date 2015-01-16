<?php
namespace IISH\View\Helper\IISH;
use Zend\Config\Config;
use Zend\View\Helper\AbstractHelper;

/**
 * DeliveryInit helper for setting up the integration of Delivery.
 *
 * @package IISH\View\Helper\IISH
 */
class DeliveryInit extends AbstractHelper {
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
     * Initializes the Delivery stylesheets.
     */
    public function stylesheets() {
        $this->view->headLink()->appendStylesheet('shopping_cart.css');
    }

    /**
     * Initializes the Delivery scripts.
     */
    public function scripts() {
        $this->view->jsobject()->addProps(array(
            'url'  => $this->deliveryUrl,
            'lang' => $this->view->layout()->userLang
        ));

        $this->view->headScript()->appendScript($this->view->jsobject()->getScript('delivery'));
        $this->view->headScript()->appendFile('delivery.js');
        $this->view->headScript()->appendFile('delivery_shop_custom/delivery.locale.nl.js');
        $this->view->headScript()->appendFile('delivery_shop_custom/delivery.locale.en.js');
        $this->view->headScript()->appendFile('delivery_shop/delivery_shop.js');
        $this->view->headScript()->appendFile('delivery_shop/example/resources/js/simpleCart.min.js');
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