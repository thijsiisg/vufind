<?php
namespace IISH\Module\Configuration;

$config = array(
    'router'          => array(
        'routes' => array(
            'navigation' => array(
                'type'    => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'       => '/Navigation/[:id[/:tab]]',
                    'constraints' => array(
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults'    => array(
                        'controller' => 'Navigation',
                        'action'     => 'Home',
                    ),
                ),
            ),
        ),
    ),
    'controllers'     => array(
        'factories'  => array(
            'record' => 'IISH\Controller\Factory::getRecordController',
        ),
        'invokables' => array(
            'cover'      => 'IISH\Controller\CoverController',
            'navigation' => 'IISH\Controller\NavigationController',
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'VuFind\CacheManager' => 'IISH\Service\Factory::getCacheManager',
        ),
    ),
    'vufind'          => array(
        'plugin_managers'   => array(
            'recorddriver' => array(
                'factories' => array(
                    'solrmarc' => 'IISH\RecordDriver\Factory::getSolrMarc',
                    'solrav'   => 'IISH\RecordDriver\Factory::getSolrAv',
                    'solread'  => 'IISH\RecordDriver\Factory::getSolrEad',
                ),
            ),
            'recordtab'    => array(
                'invokables' => array(
                    'holdingsmarc'               => 'IISH\RecordTab\HoldingsMarc',
                    'holdingsav'                 => 'IISH\RecordTab\HoldingsAv',
                    'archivecollectionsummary'   => 'IISH\RecordTab\ArchiveCollectionSummary',
                    'archivecontentlist'         => 'IISH\RecordTab\ArchiveContentList',
                    'archivecontentandstructure' => 'IISH\RecordTab\ArchiveContentAndStructure',
                    'archivesubjects'            => 'IISH\RecordTab\ArchiveSubjects',
                    'archiveaccessanduse'        => 'IISH\RecordTab\ArchiveAccessAndUse',
                    'archiveappendices'          => 'IISH\RecordTab\ArchiveAppendices',
                ),
            ),
        ),
        'recorddriver_tabs' => array(
            'IISH\RecordDriver\SolrMarc' => array(
                'tabs'       => array(
                    'Holdings'      => 'HoldingsMarc',
                    'Description'   => 'Description',
                    'TOC'           => 'TOC',
                    'UserComments'  => 'UserComments',
                    'Reviews'       => 'Reviews',
                    'Excerpt'       => 'Excerpt',
                    'HierarchyTree' => 'HierarchyTree',
                    'Map'           => 'Map',
                    'Details'       => 'StaffViewMARC',
                ),
                'defaultTab' => 'HoldingsMarc',
            ),
            'IISH\RecordDriver\SolrAv'   => array(
                'tabs'       => array(
                    'Holdings'      => 'HoldingsAv',
                    'Description'   => 'Description',
                    'TOC'           => 'TOC',
                    'UserComments'  => 'UserComments',
                    'Reviews'       => 'Reviews',
                    'Excerpt'       => 'Excerpt',
                    'HierarchyTree' => 'HierarchyTree',
                    'Map'           => 'Map',
                    'Details'       => 'StaffViewMARC',
                ),
                'defaultTab' => 'HoldingsAv',
            ),
            'IISH\RecordDriver\SolrEad'  => array(
                'tabs'       => array(
                    'ArchiveCollectionSummary'   => 'ArchiveCollectionSummary',
                    'ArchiveContentList'         => 'ArchiveContentList',
                    'ArchiveContentAndStructure' => 'ArchiveContentAndStructure',
                    'ArchiveSubjects'            => 'ArchiveSubjects',
                    'ArchiveAccessAndUse'        => 'ArchiveAccessAndUse',
                    'ArchiveAppendices'          => 'ArchiveAppendices',
                    'Holdings'                   => null,
                    'Description'                => null,
                    'TOC'                        => null,
                    'UserComments'               => null,
                    'Reviews'                    => null,
                    'Excerpt'                    => null,
                    'HierarchyTree'              => null,
                    'Map'                        => null,
                    'Details'                    => null,
                ),
                'defaultTab' => 'ArchiveCollectionSummary',
            ),
        ),
    ),
);

return $config;