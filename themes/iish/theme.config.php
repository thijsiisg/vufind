<?php
return array(
    'extends' => 'bootprint3',
    'css'     => array(),
    'js'      => array(
        'iish.js'
    ),
    'favicon' => 'favicon.ico',
    'helpers' => array(
        'factories'  => array(
            'deliveryinit' => 'IISH\View\Helper\IISH\Factory::getDeliveryInit'
        ),
        'invokables' => array(
            'jsobject' => 'IISH\View\Helper\IISH\JsObject'
        )
    )
);