<?php
namespace CG\Test\Order\Service\InvoiceNumber;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Entity as Account;
use CG\Order\Service\InvoiceNumber\Assigner;
use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Status as OrderStatus;
use CG\Order\Shared\StorageInterface as OrderStorage;
use CG\Stdlib\DateTime as CGDateTime;
use CG\Stdlib\Exception\Runtime\NotFound;
use PHPUnit\Framework\TestCase;
use Spork\ProcessManager as Spork;
use Zend\Di\Di;

/**
 * @backupGlobals disabled
 */
class AssignerIntegrationTest extends TestCase
{
    const TIMEOUT = 1;

    /**
     * @var Di $di
     */
    protected static $di;
    /**
     * @var Spork $spork
     */
    protected $spork;
    /**
     * @var OrderStorage $orderService
     */
    protected $orderService;
    /**
     * @var AccountService $accountService
     */
    protected $accountService;

    public static function setUpBeforeClass()
    {
        static::$di = $GLOBALS['di'];
    }

    protected function setUp()
    {
        $this->spork = static::$di->get(Spork::class);

        $this->orderService = $this->getMockBuilder(OrderStorage::class)->disableOriginalConstructor()->getMock();
        $this->orderService
            ->expects($this->any())
            ->method('fetchCollectionByFilter')
            ->will(
                $this->throwException(
                    new NotFound()
                )
            );

        $this->accountService = $this->getMockBuilder(AccountService::class)->disableOriginalConstructor()->getMock();
        $this->accountService
            ->expects($this->any())
            ->method('fetch')
            ->will(
                $this->returnValue(
                    new Account(
                        1,
                        1,
                        'Test',
                        '',
                        true,
                        false
                    )
                )
            );
    }

    /**
     * @group integration
     * @group race
     */
    public function testRaceCondition()
    {
        $fork1 = $this->spork->fork(
            function() {
                /**
                 * @var Assigner $assigner
                 */
                $assigner = static::$di->get(
                    Assigner::class,
                    ['orderStorage' => $this->orderService, 'accountService' => $this->accountService]
                );

                $assigner(
                    (new Order(1, 2, 'test', 1, OrderStatus::NEW_ORDER))->setPurchaseDate(date(CGDateTime::FORMAT, strtotime('+1 hour'))),
                    function() {
                        // Ensure we take longer than the timeout to process so $fork2 releases our lock
                        sleep(static::TIMEOUT * 2);
                    },
                    static::TIMEOUT
                );
            }
        );

        $fork2 = $this->spork->fork(
            function() {
                /**
                 * @var Assigner $assigner
                 */
                $assigner = static::$di->get(
                    Assigner::class,
                    ['orderStorage' => $this->orderService, 'accountService' => $this->accountService]
                );

                $assigner(
                    (new Order(1, 1, 'test', 1, OrderStatus::NEW_ORDER))->setPurchaseDate(date(CGDateTime::FORMAT, strtotime('+1 hour'))),
                    function() {
                        // Do nothing
                    },
                    static::TIMEOUT
                );
            }
        );

        $this->spork->wait();

        if ($fork1->getError()) {
            $this->fail('Fork 1 Errored - ' . $fork1->getError()->getMessage());
        }

        if ($fork2->getError()) {
            $this->fail('Fork 2 Errored - ' . $fork2->getError()->getMessage());
        }
    }
}
