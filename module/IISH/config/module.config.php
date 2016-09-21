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
            'cover'       => 'IISH\Controller\CoverController',
            'file'        => 'IISH\Controller\FileController',
            'harvest'     => 'IISH\Controller\HarvestController',
            'iish'        => 'IISH\Controller\IISHController',
            'my-research' => 'IISH\Controller\MyResearchController',
            'navigation'  => 'IISH\Controller\NavigationController',
            'search'      => 'IISH\Controller\SearchController',
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'VuFind\AuthManager'  => 'IISH\Auth\Factory::getManager',
            'VuFind\CacheManager' => 'IISH\Service\Factory::getCacheManager',
        ),
    ),
    'view_manager' => array(
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),
    'vufind'          => array(
        'plugin_managers'   => array(
            'auth'           => array(
                'invokables' => array(
                    'ldap'      => 'IISH\Auth\LDAP',
                    'multiauth' => 'IISH\Auth\MultiAuth'
                ),
            ),
            'content_covers' => array(
                'factories' => array(
                    'iish' => 'IISH\Content\Covers\Factory::getIISH',
                ),
            ),
            'recorddriver'   => array(
                'factories' => array(
                    'solrmarc'     => 'IISH\RecordDriver\Factory::getSolrMarc',
                    'solrav'       => 'IISH\RecordDriver\Factory::getSolrAv',
                    'solread'      => 'IISH\RecordDriver\Factory::getSolrEad',
                    'solreci'      => 'IISH\RecordDriver\Factory::getSolrEci',
                    'solrfulltext' => 'IISH\RecordDriver\Factory::getSolrFullText',
                ),
            ),
            'recordtab'      => array(
                'invokables' => array(
                    'holdingsmarc'               => 'IISH\RecordTab\HoldingsMarc',
                    'archivecollectionsummary'   => 'IISH\RecordTab\ArchiveCollectionSummary',
                    'archivecontentlist'         => 'IISH\RecordTab\ArchiveContentList',
                    'archivecontentandstructure' => 'IISH\RecordTab\ArchiveContentAndStructure',
                    'archivesubjects'            => 'IISH\RecordTab\ArchiveSubjects',
                    'archiveaccessanduse'        => 'IISH\RecordTab\ArchiveAccessAndUse',
                    'archiveappendices'          => 'IISH\RecordTab\ArchiveAppendices',
                    'holdingseci'                => 'IISH\RecordTab\HoldingsECI',
                    'staffvieweci'               => 'IISH\RecordTab\StaffViewECI',
                    'textsearch'                 => 'IISH\RecordTab\TextSearch',
                ),
            ),
            'search_backend' => array(
                'factories' => array(
                    'Solr'         => 'IISH\Search\Factory\SolrDefaultBackendFactory',
                    'SolrFullText' => 'IISH\Search\Factory\SolrFullTextBackendFactory',
                ),
            ),
            'search_params'  => array(
                'abstract_factories' => array('IISH\Search\Params\PluginFactory'),
            ),
            'search_results' => array(
                'abstract_factories' => array('IISH\Search\Results\PluginFactory'),
                'factories'          => array(
                    'solr' => 'IISH\Search\Results\Factory::getSolr',
                ),
            ),
        ),
        'recorddriver_tabs' => array(
            'IISH\RecordDriver\SolrMarc' => array(
                'tabs'       => array(
                    'Holdings'      => 'HoldingsMarc',
                    'Description'   => null,
                    'TOC'           => 'TOC',
                    'UserComments'  => 'UserComments',
                    'Reviews'       => 'Reviews',
                    'Excerpt'       => 'Excerpt',
                    'HierarchyTree' => 'HierarchyTree',
                    'Map'           => 'Map',
                    'TextSearch'    => 'TextSearch',
                    'Details'       => 'StaffViewMARC',
                ),
                'defaultTab' => 'HoldingsMarc',
            ),
            'IISH\RecordDriver\SolrAv'   => array(
                'tabs'       => array(
                    'Holdings'      => 'HoldingsMarc',
                    'Description'   => null,
                    'TOC'           => 'TOC',
                    'UserComments'  => 'UserComments',
                    'Reviews'       => 'Reviews',
                    'Excerpt'       => 'Excerpt',
                    'HierarchyTree' => 'HierarchyTree',
                    'Map'           => 'Map',
                    'TextSearch'    => 'TextSearch',
                    'Details'       => 'StaffViewMARC',
                ),
                'defaultTab' => 'HoldingsAv',
            ),
            'IISH\RecordDriver\SolrEad'  => array(
                'tabs'       => array(
                    'ArchiveCollectionSummary'   => 'ArchiveCollectionSummary',
                    'ArchiveContentList'         => 'ArchiveContentList',
                    'ArchiveContentAndStructure' => 'ArchiveContentAndStructure',
                    'TextSearch'                 => 'TextSearch',
                    'ArchiveAccessAndUse'        => 'ArchiveAccessAndUse',
                    'ArchiveSubjects'            => 'ArchiveSubjects',
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
            'IISH\RecordDriver\SolrEci'  => array(
                'tabs'       => array(
                    'Holdings'      => 'HoldingsECI',
                    'Description'   => null,
                    'TOC'           => null,
                    'UserComments'  => null,
                    'Reviews'       => null,
                    'Excerpt'       => null,
                    'HierarchyTree' => null,
                    'Map'           => null,
                    'Details'       => 'StaffViewECI',
                ),
                'defaultTab' => 'HoldingsECI',
            ),
        ),
    ),
);

// Define static routes -- Controller/Action strings
$staticRoutes = array(
    'IISH/About', 'IISH/Databases', 'Order/Home'
);

// Build static routes
foreach ($staticRoutes as $route) {
    list($controller, $action) = explode('/', $route);
    $routeName = str_replace('/', '-', strtolower($route));
    $config['router']['routes'][$routeName] = array(
        'type'    => 'Zend\Mvc\Router\Http\Literal',
        'options' => array(
            'route'    => '/' . $route,
            'defaults' => array(
                'controller' => $controller,
                'action'     => $action,
            )
        )
    );
}

$config['router']['routes']['record-search'] = array(
    'type'    => 'Zend\Mvc\Router\Http\Segment',
    'options' => array(
        'route'       => '/Record/[:id]/Search',
        'constraints' => array(
            'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
            'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
        ),
        'defaults'    => array(
            'controller' => 'Record',
            'action'     => 'Search',
        )
    )
);

$config['router']['routes']['record-digital'] = array(
    'type'    => 'Zend\Mvc\Router\Http\Segment',
    'options' => array(
        'route'       => '/Record/[:id]/Digital',
        'constraints' => array(
            'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
            'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
        ),
        'defaults'    => array(
            'controller' => 'Record',
            'action'     => 'Digital',
        )
    )
);

return $config;