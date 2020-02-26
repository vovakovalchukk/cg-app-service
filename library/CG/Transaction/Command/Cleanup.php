<?php
namespace CG\Transaction\Command;

use CG\Transaction\Command\Cleanup\TransactionKeyMap;
use CG\Transaction\Entity as Transaction;
use CG\Transaction\Predis\ClearTransaction;
use Predis\Client as Predis;
use Predis\Collection\Iterator\Keyspace as PredisKeyspace;
use Symfony\Component\Console\Output\OutputInterface;

class Cleanup
{
    /** @var Predis */
    protected $predis;

    public function __construct(Predis $predis)
    {
        $this->predis = $predis;
        $this->predis->getProfile()->defineCommand('cleanupTransaction', ClearTransaction::class);
        $this->predis->script('load', (new ClearTransaction)->getScript());
    }

    public function __invoke(OutputInterface $output, int $chunkSize = null)
    {
        $count = 0;
        foreach ($this->fetchChunkedTransactionActionKeys($chunkSize ?? 50) as $transactionActionKeys) {
            $batch = $this->predis->transaction();
            foreach ($this->mapTransactionActionsToTransactionKeys($transactionActionKeys) as $transactionAction => $transactionKey) {
                $batch->cleanupTransaction($transactionKey, $transactionAction);
            }
            foreach ($batch->execute() as $status) {
                $count++;
                $output->write($status ? ',' : '.');
            }
            sleep(1);
        }
        if ($count > 0) {
            $output->writeln('');
        }
    }

    protected function fetchTransactionActionKeys(): \Traversable
    {
        return new PredisKeyspace($this->predis, sprintf('%s%s*', Transaction::ACTION_PREFIX, Transaction::SEPARATOR));
    }

    protected function fetchChunkedTransactionActionKeys(int $chunkSize): \Traversable
    {
        $chunk = [];
        foreach ($this->fetchTransactionActionKeys() as $transactionActionKey) {
            $chunk[] = $transactionActionKey;
            if (count($chunk) >= $chunkSize) {
                yield $chunk;
                $chunk = [];
            }
        }

        if (!empty($chunk)) {
            yield $chunk;
        }
    }

    protected function mapTransactionActionsToTransactionKeys(array $transactionActionKeys): \Generator
    {
        foreach ($transactionActionKeys as $transactionAction) {
            [, $timestamp, $transactionKey] = explode(Transaction::SEPARATOR, $transactionAction, 3);
            yield new TransactionKeyMap($transactionKey, $transactionAction, $timestamp);
        }
    }
}