<?php
namespace IISH\Search\Results;
use Zend\ServiceManager\ServiceManager;
use VuFind\Search\Results\Factory as VuFindFactory;

/**
 * Search Results Object Factory Class.
 *
 * Override to make sure the overridden Results class is used.
 *
 * @package IISH\Search\Results
 */
class Factory extends VuFindFactory {

    /**
     * Factory for Solr results object.
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return \IISH\Search\Solr\Results
     */
    public static function getSolr(ServiceManager $sm) {
        $factory = new PluginFactory();
        $solr = $factory->createServiceWithName($sm, 'solr', 'Solr');
        $config = $sm->getServiceLocator()->get('VuFind\Config')->get('config');
        $spellConfig = isset($config->Spelling) ? $config->Spelling : null;
        $solr->setSpellingProcessor(
            new \VuFind\Search\Solr\SpellingProcessor($spellConfig)
        );

        return $solr;
    }
}