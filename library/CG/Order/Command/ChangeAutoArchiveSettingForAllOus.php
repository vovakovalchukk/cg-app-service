<?php
namespace CG\Order\Command;

use CG\Order\Shared\Command\AutoArchiveOrders;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\Settings\Order\AutoArchiveTimeframe;
use CG\Settings\Order\Entity as OrderSettings;

class ChangeAutoArchiveSettingForAllOus extends AutoArchiveOrders
{
    public const LOG_CODE = 'ChangeTimeFrameAndAutoArchiveOrders';
    public const LOG_INVOKED = 'ChangeTimeFrame and AutoArchiveOrders invoked';

    public function __invoke()
    {
        $this->logDebug(static::LOG_INVOKED, [], [static::LOG_CODE]);
        $organisationUnits = $this->fetchAllOrganisationUnits();
        foreach ($organisationUnits as $organisationUnit) {
            $this->processOrganisationUnit($organisationUnit);
            $this->getLogger() && $this->getLogger()->flushLogs();
        }

        $this->logDebug(static::LOG_DONE, [], [static::LOG_CODE]);
    }

    protected function getOrderSettingsForOu(OrganisationUnit $organisationUnit): ?OrderSettings
    {
        $settings = parent::getOrderSettingsForOu($organisationUnit);
        if ($settings->getAutoArchiveTimeframe() == AutoArchiveTimeframe::DEFAULT_TIMEFRAME) {
            return null;
        }

        $settings->setAutoArchiveTimeframe(AutoArchiveTimeframe::DEFAULT_TIMEFRAME);
        try {
            $this->orderSettingsService->save($settings);
            return $settings;
        } catch (\Throwable $throwable) {
            $this->logWarningException($throwable, 'Settings Saving Exception', [], static::LOG_CODE);
            return null;
        }
    }
}