<?php
namespace IISH\Controller;
use IISH\Harvester\OAI;
use Zend\Console\Console;
use VuFindConsole\Controller\HarvestController as VuFindHarvestController;

/**
 * This controller handles various command-line tools.
 *
 * Override only to make sure our overridden OAI class is being used.
 *
 * @package IISH\Controller
 */
class HarvestController extends VuFindHarvestController {

    /**
     * Harvest OAI-PMH records.
     *
     * @return \Zend\Console\Response
     */
    public function harvestoaiAction() {
        $this->checkLocalSetting();

        // Parse switches:
        $this->consoleOpts->addRules(
            array('from-s' => 'Harvest start date', 'until-s' => 'Harvest end date')
        );
        $from = $this->consoleOpts->getOption('from');
        $until = $this->consoleOpts->getOption('until');

        // Read Config files
        $configFile = \VuFind\Config\Locator::getConfigPath('oai.ini', 'harvest');
        $oaiSettings = @parse_ini_file($configFile, true);
        if (empty($oaiSettings)) {
            Console::writeLine("Please add OAI-PMH settings to oai.ini.");

            return $this->getFailureResponse();
        }

        // If first command line parameter is set, see if we can limit to just the
        // specified OAI harvester:
        $argv = $this->consoleOpts->getRemainingArgs();
        if (isset($argv[0])) {
            if (isset($oaiSettings[$argv[0]])) {
                $oaiSettings = array($argv[0] => $oaiSettings[$argv[0]]);
            }
            else {
                Console::writeLine("Could not load settings for {$argv[0]}.");

                return $this->getFailureResponse();
            }
        }

        // Loop through all the settings and perform harvests:
        $processed = 0;
        foreach ($oaiSettings as $target => $settings) {
            if (!empty($target) && !empty($settings)) {
                Console::writeLine("Processing {$target}...");
                try {
                    $client = $this->getServiceLocator()->get('VuFind\Http')
                        ->createClient();
                    $harvest = new OAI($target, $settings, $client, $from, $until);
                    $harvest->launch();
                }
                catch (\Exception $e) {
                    Console::writeLine($e->getMessage());

                    return $this->getFailureResponse();
                }
                $processed++;
            }
        }

        // All done.
        Console::writeLine(
            "Completed without errors -- {$processed} source(s) processed."
        );

        return $this->getSuccessResponse();
    }
}