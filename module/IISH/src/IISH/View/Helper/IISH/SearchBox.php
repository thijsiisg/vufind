<?php
namespace IISH\View\Helper\IISH;
use VuFind\View\Helper\Root\SearchBox as VuFindSearchBox;

/**
 * Search box view helper
 *
 * Override to add support for full text search.
 *
 * @package IISH\View\Helper\IISH
 */
class SearchBox extends VuFindSearchBox {

    /**
     * Determine if full text search is enabled.
     *
     * @param string $activeHandler Active search handler
     *
     * @return bool True if full text search is enabled.
     */
    public function isFullTextSearchEnabled($activeHandler) {
        return ($activeHandler === 'AllFieldsFullText');
    }

    /**
     * Support method for getHandlers() -- load basic settings.
     *
     * @param string $activeSearchClass Active search class ID
     * @param string $activeHandler     Active search handler
     *
     * @return array
     */
    protected function getBasicHandlers($activeSearchClass, $activeHandler) {
        $handlers = array();
        $options = $this->optionsManager->get($activeSearchClass);
        foreach ($options->getBasicHandlers() as $searchVal => $searchDesc) {
            $handlers[] = array(
                'value'    => $searchVal, 'label' => $searchDesc, 'indent' => false,
                'selected' => (($activeHandler == $searchVal) ||
                        (($activeHandler === 'AllFieldsFullText') && ($searchVal === 'AllFields')))
            );
        }
        return $handlers;
    }

    /**
     * Support method for getHandlers() -- load combined settings.
     *
     * @param string $activeSearchClass Active search class ID
     * @param string $activeHandler     Active search handler
     *
     * @return array
     */
    protected function getCombinedHandlers($activeSearchClass, $activeHandler) {
        // Build settings:
        $handlers = array();
        $selectedFound = false;
        $backupSelectedIndex = false;
        $settings = $this->getCombinedHandlerConfig($activeSearchClass);
        $typeCount = count($settings['type']);
        for ($i = 0; $i < $typeCount; $i++) {
            $type = $settings['type'][$i];
            $target = $settings['target'][$i];
            $label = $settings['label'][$i];

            if ($type == 'VuFind') {
                $options = $this->optionsManager->get($target);
                $j = 0;
                $basic = $options->getBasicHandlers();
                if (empty($basic)) {
                    $basic = array('' => '');
                }
                foreach ($basic as $searchVal => $searchDesc) {
                    $j++;
                    $selected = $target == $activeSearchClass
                        && (($activeHandler == $searchVal) ||
                            (($activeHandler === 'AllFieldsFullText') && ($searchVal === 'AllFields')));
                    if ($selected) {
                        $selectedFound = true;
                    }
                    else if ($backupSelectedIndex === false
                        && $target == $activeSearchClass
                    ) {
                        $backupSelectedIndex = count($handlers);
                    }
                    $handlers[] = array(
                        'value'    => $type . ':' . $target . '|' . $searchVal,
                        'label'    => $j == 1 ? $label : $searchDesc,
                        'indent'   => $j == 1 ? false : true,
                        'selected' => $selected
                    );
                }
            }
            else if ($type == 'External') {
                $handlers[] = array(
                    'value'  => $type . ':' . $target, 'label' => $label,
                    'indent' => false, 'selected' => false
                );
            }
        }

        // If we didn't find an exact match for a selected index, use a fuzzy
        // match:
        if (!$selectedFound && $backupSelectedIndex !== false) {
            $handlers[$backupSelectedIndex]['selected'] = true;
        }
        return $handlers;
    }
}