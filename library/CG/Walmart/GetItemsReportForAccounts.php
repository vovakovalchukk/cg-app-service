<?php
namespace CG\Walmart\Command;

use CG\Channel\Command\Service as CommandService;
use CG\Account\Shared\Collection as AccountCollection;
use CG\Account\Shared\Entity as AccountEntity;
use CG\Cilex\ModulusAwareInterface;
use CG\Cilex\ModulusTrait;
use CG\Walmart\Gearman\Generator\FetchInventoryReportForAccount;

class GetItemsReportForAccounts implements ModulusAwareInterface
{
    use ModulusTrait;

    const CHANNEL_WALMART = 'walmart';

    /** @var CommandService */
    protected $commandService;
    /** @var FetchInventoryReportForAccount */
    protected $jobGenerator;

    public function __construct(CommandService $commandService, FetchInventoryReportForAccount $jobGenerator)
    {
        $this->commandService = $commandService;
        $this->jobGenerator = $jobGenerator;
    }

    public function __invoke(): void
    {
        $accounts = $this->fetchEnabledAccounts();
        /** @var AccountEntity $account */
        foreach ($accounts as $account) {
            ($this->jobGenerator)($account->getId());
        }
    }

    protected function fetchEnabledAccounts(): AccountCollection
    {
        $accounts = $this->commandService->getAccounts([static::CHANNEL_WALMART]);
        $this->filterCollection($accounts);
        return $accounts;
    }
}