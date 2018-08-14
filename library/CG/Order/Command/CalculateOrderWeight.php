<?php
namespace CG\Order\Command;

use CG\Order\Client\Gearman\Generator\CalculateOrderWeight as CalculateOrderWeightGearmanJobGenerator;
use CG\Order\Service\Filter as OrderFilter;
use CG\Order\Service\Service as OrderService;
use CG\Order\Shared\Collection as Orders;
use CG\Order\Shared\Entity as Order;
use CG\Stdlib\Exception\Runtime\NotFound;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class CalculateOrderWeight
{
    protected const ORDER_PAGE_LIMIT = 250;

    /** @var OrderService */
    protected $orderService;
    /** @var CalculateOrderWeightGearmanJobGenerator */
    protected $calculateOrderWeightGearmanJobGenerator;
    /** @var OutputInterface */
    protected $output;

    public function __construct(
        OrderService $orderService,
        CalculateOrderWeightGearmanJobGenerator $calculateOrderWeightGearmanJobGenerator,
        OutputInterface $output
    ) {
        $this->orderService = $orderService;
        $this->calculateOrderWeightGearmanJobGenerator = $calculateOrderWeightGearmanJobGenerator;
        $this->output = $output;
    }

    public function __invoke(array $ouIds = [], array $accountIds = [], bool $includeArchived = false)
    {
        foreach ($this->getMatchingOrders($ouIds, $accountIds, $includeArchived) as $order) {
            $this->calculateOrderWeightGearmanJobGenerator->generateJobForOrder($order);
        }
    }

    /**
     * @return Order[]
     */
    protected function getMatchingOrders(array $ouIds, array $accountIds, bool $includeArchived): \Generator
    {
        $this->output->writeln('Looking for matching orders...');

        $filter = (new OrderFilter(static::ORDER_PAGE_LIMIT))
            ->setRootOrganisationUnitId($ouIds)
            ->setAccountId($accountIds)
            ->setArchived($includeArchived ? null : false);

        $page = 1;
        do {
            try {
                /** @var Orders $orders */
                $orders = $this->orderService->fetchCollectionByFilter($filter->setPage($page));

                if (!isset($progressBar)) {
                    $progressBar = new ProgressBar($this->output, $orders->getTotal());
                    $progressBar->display();
                }

                foreach ($orders as $order) {
                    yield $order;
                    $progressBar->advance();
                }
            } catch (NotFound $exception) {
                break;
            }
        } while ($page++);

        if (isset($progressBar)) {
            $progressBar->clear();
            $this->output->writeln(sprintf('<fg=green>Generated %d jobs</>', $progressBar->getMaxSteps()));
        } else {
            $this->output->writeln('<fg=red>No orders found!</>');
        }
    }
}