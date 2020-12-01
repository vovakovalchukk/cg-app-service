<?php
namespace CG\Order\Command;

use CG\Order\Shared\Command\AutoArchiveOrders;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\Settings\Order\AutoArchiveTimeframe;

class ChangeAutoArchiveSettingForAllOus extends AutoArchiveOrders
{
    public function __invoke()
    {
        $this->logDebug(static::LOG_INVOKED, [], [static::LOG_CODE, 'Invoked']);

        $organisationUnits = $this->fetchAllOrganisationUnits();
        // Perform modulus on collection to spread them out over time
        $this->filterCollection($organisationUnits);
        $this->logDebug(static::LOG_COUNT_MODULATED, [count($organisationUnits)], [static::LOG_CODE, 'CountModulated']);

        foreach ($organisationUnits as $organisationUnit) {
            $this->processOrganisationUnit($organisationUnit);
            $this->getLogger() && $this->getLogger()->flushLogs();
        }

        $this->logDebug(static::LOG_DONE, [], [static::LOG_CODE, 'Done']);
    }

    protected function updateAutoArchiveTimeframeForOU(OrganisationUnit $organisationUnit)
    {
        $settings = $this->getOrderSettingsForOu($organisationUnit);

        if ($settings->getAutoArchiveTimeframe() == AutoArchiveTimeframe::ONE_YEAR) {
            return null;
        }


    }

    protected function getOrderSettingsForOu(OrganisationUnit $organisationUnit) {
        $settings = parent::getOrderSettingsForOu($organisationUnit);
        if ($settings->getAutoArchiveTimeframe() == AutoArchiveTimeframe::ONE_YEAR) {
            return null;
        }
    }
}