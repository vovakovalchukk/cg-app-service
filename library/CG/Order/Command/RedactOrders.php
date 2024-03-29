<?php
namespace CG\Order\Command;

use CG\Db\Mysqli;
use CG\Db\Query\Where;
use CG\Order\Client\Gearman\Generator\RedactOrder as GearmanJobGenerator;
use CG\Order\Service\RedactLocker;
use CG\Order\Service\Storage\Persistent\Db;
use CG\Order\Shared\Address\Redacted as RedactedAddress;
use CG\Order\Shared\Status as OrderStatus;
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

    public function __invoke(OutputInterface $output, string $channel, string $date = null, int $limit = null)
    {
        $date = $date ?? static::DEFAULT_DATE;
        $dateTime = (new DateTime($date))->resetTime();
        $output->writeln(sprintf('Redacting %s orders older than <comment>%s (%s)</comment>', $channel, $date, $dateTime->stdDateFormat()));

        $progressBar = $this->getProgressBar($output);
        $output->writeln(sprintf('Fetching matching %s orders... ', $channel));
        $progressBar->start();

        foreach ($this->matchOrders($channel, $dateTime, $limit) as $orderId) {
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
            return sprintf('%s job%s', number_format($jobs), $jobs !== 1 ? 's' : '');
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
    protected function matchOrders(string $channel, DateTime $dateTime, int $maxResults = null): iterable
    {
        $minimumId = null;
        for ($offset = 0; ; $offset += $limit) {
            $limit = 1000;
            if ($maxResults !== null) {
                $limit = min($limit, $maxResults - $offset);
            }

            if ($limit <= 0) {
                return;
            }

            $sql = sprintf('SELECT `id` FROM `%s`', Db::ORDER_TABLE_NAME);
            $where = (new Where())
                ->equals('`channel`', 's', $channel)
                ->range('`id`', 's', $minimumId)
                ->append(
                    (new Where(Where::SEPERATOR_OR))
                        ->expression('`dispatchDate` < ?', [['s', $dateTime->stdFormat()]])
                        ->expression(
                            '(`dispatchDate` IS NULL AND `purchaseDate` < ? AND `status` NOT IN (?, ?))',
                            [
                                ['s', $dateTime->stdFormat()],
                                ['s', OrderStatus::NEW_ORDER],
                                ['s', OrderStatus::AWAITING_PAYMENT],
                            ]
                        )
                )
                ->append(
                    (new Where(Where::SEPERATOR_OR))
                        ->notEquals('`billingAddressId`', 's', RedactedAddress::ID)
                        ->notEquals('`shippingAddressId`', 's', RedactedAddress::ID)
                        ->notEquals('`fulfilmentAddressId`', 's', RedactedAddress::ID)
                        ->append(
                            (new Where())
                                ->equals('`buyerMessageRedacted`', 'i', false)
                                ->notEquals('`buyerMessage`', 's', '')
                        )
                        ->exists(
                            'SELECT `giftWrap`.`id` FROM `giftWrap` JOIN `item` ON `giftWrap`.`orderItemId` = `item`.`id`',
                            (new Where())
                                ->equals('`giftWrap`.`giftWrapRedacted`', 'i', false)
                                ->notEquals('`giftWrap`.`giftWrapMessage`', 's', '')
                                ->expression(sprintf('`item`.`orderId` = `%s`.`id`', Db::ORDER_TABLE_NAME))
                        )
                );

            $results = iterator_to_array(
                $this->mysqli->query(
                    $sql . $where . sprintf(' ORDER BY `id` ASC LIMIT %d', $limit),
                    $where->getWhereParameters(),
                    function (\mysqli_result $result): iterable {
                        while ($order = $result->fetch_assoc()) {
                            yield $order['id'];
                        }
                    }
                )
            );
            yield from $results;
            $count = count($results);
            $minimumId = end($results);
            if ($count < $limit) {
                return;
            }
        }
    }
}