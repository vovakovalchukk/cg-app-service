<?php
namespace CG\App\Command;

use CG\Account\Client\StorageInterface as AccountStorage;
use CG\Channel\Gearman\Generator\OrderDownload as JobGenerator;

class OrderDownload
{
    protected $accountStorage;
    protected $jobGenerator;

    public function __construct(AccountStorage $accountStorage, JobGenerator $jobGenerator)
    {
        $this->setAccountStorage($accountStorage)
            ->setJobGenerator($jobGenerator);
    }

    public function downloadOrders($channel, $getToTime)
    {
        $accounts = $this->getAccountStorage()->fetchCollectionByChannel($channel, 'all');
        $this->getJobGenerator()->generateJobs($accounts, $getToTime, $channel);
    }

    public function setAccountStorage(AccountStorage $accountStorage)
    {
        $this->accountStorage = $accountStorage;
        return $this;
    }

    public function getAccountStorage()
    {
        return $this->accountStorage;
    }

    public function setJobGenerator(JobGenerator $jobGenerator)
    {
        $this->jobGenerator = $jobGenerator;
        return $this;
    }

    public function getJobGenerator()
    {
        return $this->jobGenerator;
    }
}