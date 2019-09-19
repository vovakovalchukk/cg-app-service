<?php
namespace CG\Stripe\Command;

use CG\OrganisationUnit\Collection as OrganisationUnitCollection;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Stdlib\DateTime as StdlibDateTime;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Stripe\UsageRecord\Creator as UsageRecordCreator;
use DateTime;

class SendUsage implements LoggerAwareInterface
{
    use LogTrait;

    protected const LOG_CODE = 'StripeSendUsage';
    protected const LOG_START = 'Gathering and sending usage to Stripe between %s and %s for OUs: %s';

    /** @var OrganisationUnitService */
    protected $organisationUnitService;
    /** @var UsageRecordCreator */
    protected $usageRecordCreator;

    public function __construct(
        OrganisationUnitService $organisationUnitService,
        UsageRecordCreator $usageRecordCreator
    ) {
        $this->organisationUnitService = $organisationUnitService;
        $this->usageRecordCreator = $usageRecordCreator;
    }

    public function __invoke(DateTime $usageFrom = null, DateTime $usageTo = null, int $organisationUnitId = null): void
    {
        $usageFrom = $this->sanitiseFromDate($usageFrom);
        $usageTo = $this->sanitiseToDate($usageTo);
        $this->logDebug(static::LOG_START, [$usageFrom->format(StdlibDateTime::FORMAT), $usageTo->format(StdlibDateTime::FORMAT), $organisationUnitId ?? 'all'], [static::LOG_CODE, 'Start']);
        $rootOus = $this->fetchRootOusToProcess($organisationUnitId);
        ($this->usageRecordCreator)($usageFrom, $usageTo, $rootOus);
    }

    protected function sanitiseFromDate(?DateTime $usageFrom ): DateTime
    {
        $usageFrom = $usageFrom ?? new DateTime('yesterday');
        $usageFrom->setTime(0, 0, 0);
        return $usageFrom;
    }

    protected function sanitiseToDate(?DateTime $usageTo): DateTime
    {
        $usageTo = $usageTo ?? new DateTime('yesterday');
        $usageTo->setTime(23, 59, 59);
        return $usageTo;
    }

    protected function fetchRootOusToProcess(int $organisationUnitId = null): OrganisationUnitCollection
    {
        if (!$organisationUnitId) {
            return $this->organisationUnitService->fetchRootOus('all', 1);
        }
        /** @var OrganisationUnit $organisationUnit */
        $organisationUnit = $this->organisationUnitService->fetch($organisationUnitId);
        $rootOu = $organisationUnit->getRootEntity();
        $collection = new OrganisationUnitCollection(OrganisationUnit::class, 'fetch', ['id' => $rootOu->getId()]);
        $collection->attach($rootOu);
        return $collection;
    }
}