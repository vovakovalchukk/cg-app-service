<?php
namespace CG\Order\Command;

use CG\Db\Mysqli;
use CG\Db\Query\Where;
use CG\Order\Client\Gearman\Generator\RedactOrder as GearmanJobGenerator;
use CG\Order\Service\RedactLocker;
use CG\Order\Service\Storage\Persistent\Db;
use CG\Order\Shared\Address\Redacted as RedactedAddress;
use CG\Stdlib\DateTime;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class RedactOrders
{
    const DEFAULT_DATE = '30 days ago';

    /** @var Mysqli */
    protected $mysqli;
    /** @var RedactLocker */
    protected $redactLocker;
    /** @var GearmanJobGenerator */
    protected $gearmanJobGenerator;

    public function __construct(Mysqli $mysqli, RedactLocker $redactLocker, GearmanJobGenerator $gearmanJobGenerator)
    {
        $this->mysqli = $mysqli;
        $this->redactLocker = $redactLocker;
        $this->gearmanJobGenerator = $gearmanJobGenerator;
    }

    public function __invoke(OutputInterface $output, string $channel, string $date = null)
    {
        $date = $date ?? static::DEFAULT_DATE;
        $dateTime = (new DateTime($date))->resetTime();
        $output->writeln(sprintf('Redacting %s orders older than <comment>%s (%s)</comment>', $channel, $date, $dateTime->stdDateFormat()));

        $progressBar = $this->getProgressBar($output);
        $output->writeln(sprintf('Fetching matching %s orders... ', $channel));
        $progressBar->start();

        foreach ($this->matchOrders($channel, $dateTime) as $orderId) {
            $progressBar->setMessage($orderId, 'orderId');

            if (!$this->redactLocker->canRedact($orderId)) {
                $progressBar->setMessage(true, 'skip');
                $progressBar->display();
                $progressBar->setMessage(null, 'orderId');
                $progressBar->setMessage(null, 'skip');
                continue;
            }

            $progressBar->display();
            $progressBar->setMessage(null, 'orderId');

            ($this->gearmanJobGenerator)(
                $orderId
            );

            $progressBar->advance();
        }

        $progressBar->finish();
    }

    protected function getProgressBar(OutputInterface $output): ProgressBar
    {
        $format = 'Generated %jobs% in %elapsed%' . "\n";
        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $format = '%orderId%' . $format;
        }

        ProgressBar::setPlaceholderFormatterDefinition('jobs', function (ProgressBar $progressBar) {
            $jobs = $progressBar->getProgress();
            return sprintf('%d job%s', $jobs, $jobs !== 1 ? 's' : '');
        });
        ProgressBar::setPlaceholderFormatterDefinition('orderId', function (ProgressBar $progressBar) {
            $orderId = $progressBar->getMessage('orderId');
            if ($progressBar->getMessage('skip')) {
                return $orderId ? sprintf("\033[2mSkipping order %s\033[0m\n", $orderId) : '';
            }
            return $orderId ? sprintf("\033[2mGenerating job for order %s\033[0m\n", $orderId) : '';
        });

        $progressBar = new ProgressBar($output);
        $progressBar->setMessage(null, 'orderId');
        $progressBar->setMessage(null, 'skip');
        $progressBar->setFormat($format);
        return $progressBar;
    }

    /**
     * @return string[]
     */
    protected function matchOrders(string $channel, DateTime $dateTime): iterable
    {
        $sql = sprintf('SELECT `id` FROM `%s`', Db::ORDER_TABLE_NAME);
        $where = (new Where())
            ->equals('`channel`', 's', $channel)
            ->append(
                (new Where(Where::SEPERATOR_OR))
                    ->expression('`dispatchDate` < ?', [['s', $dateTime->stdFormat()]])
                    ->expression('(`dispatchDate` IS NULL AND `purchaseDate` < ?)', [['s', $dateTime->stdFormat()]])
            )
            ->append(
                (new Where(Where::SEPERATOR_OR))
                    ->notEquals('`billingAddressId`', 's', RedactedAddress::ID)
                    ->notEquals('`shippingAddressId`', 's', RedactedAddress::ID)
                    ->notEquals('`fulfilmentAddressId`', 's', RedactedAddress::ID)
            );

        yield from $this->mysqli->query(
            $sql . $where,
            $where->getWhereParameters(),
            function(\mysqli_result $result): iterable {
                while ($order = $result->fetch_assoc()) {
                    yield $order['id'];
                }
            }
        );
    }
}