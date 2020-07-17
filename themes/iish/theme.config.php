<?php
return array(
    'extends' => 'bootprint3',
    'css'     => array(),
    'js'      => array(
        'iish.js',
        '../vendor/jquery-slimscroll/jquery.slimscroll.js'
    ),
    'favicon' => 'favicon.ico',
    'helpers' => array(
        'factories'  => array(
            'deliveryinit' => 'IISH\View\Helper\IISH\Factory::getDeliveryInit',
            'searchbox'    => 'IISH\View\Helper\IISH\Factory::getSearchBox',
			'iishnetwork'     => 'IISH\View\Helper\IISH\Factory::getIISHNetwork'
        ),
        'invokables' => array(
            'jsobject' => 'IISH\View\Helper\IISH\JsObject'
        )
    )
);