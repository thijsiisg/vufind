<?php
namespace IISH\Module\Configuration;

$config = array(
    'controllers'     => array(
        'factories' => array(
            'record' => 'IISH\Controller\Factory::getRecordController',
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'VuFind\CacheManager' => 'IISH\Service\Factory::getCacheManager',
        ),
    ),
    'vufind'          => array(
        'plugin_managers' => array(
            'recorddriver' => array(
                'factories' => array(
                    'solrmarc' => 'IISH\RecordDriver\Factory::getSolrMarc',
                    'solrav'   => 'IISH\RecordDriver\Factory::getSolrAv',
                    'solread'  => 'IISH\RecordDriver\Factory::getSolrEad',
                ),
            ),
        ),
    ),
);

return $config;