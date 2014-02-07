<?php
namespace CG\App\Test\Command;

use CG\App\Command\OrderDownload as Command;
use CG\Account\Client\StorageInterface as AccountStorage;
use CG\Channel\Gearman\Generator\OrderDownload as Generator;

class DownloadOrdersTest extends \PHPUnit_Framework_TestCase
{
    protected $command;

    public function setUp()
    {
        $accountApi = $this->getMock(AccountStorage::class);

        $generator = $this->getMockBuilder(Generator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->command = new Command($accountApi, $generator);
    }
}