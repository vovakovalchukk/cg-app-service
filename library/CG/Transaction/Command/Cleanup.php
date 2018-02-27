<?php
namespace CG\Transaction\Command;

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
    }

    public function __invoke(OutputInterface $output)
    {
        foreach ($this->fetchChunkedTransactionActionKeys() as $transactionActionKeys) {
            $transaction = $this->predis->transaction();
            foreach ($this->mapTransactionActionsToTransactionKeys($transactionActionKeys) as $transactionAction => $transactionKey) {
                $transaction->cleanupTransaction($transactionKey, $transactionAction);
            }
            foreach ($transaction->execute() as $status) {
                $output->write($status ? ',' : '.');
            }
        }
        $output->writeln('');
    }

    protected function fetchTransactionActionKeys(): \Traversable
    {
        return new PredisKeyspace($this->predis, sprintf('%s%s*', Transaction::ACTION_PREFIX, Transaction::SEPARATOR));
    }

    protected function fetchChunkedTransactionActionKeys(int $chunkSize = 100): \Traversable
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

    protected function mapTransactionActionsToTransactionKeys(array $transactionActionKeys): array
    {
        $transactionActionToKeyMap = [];
        foreach ($transactionActionKeys as $transactionAction) {
            $transactionActionToKeyMap[$transactionAction] = explode(Transaction::SEPARATOR, $transactionAction, 3)[2];
        }
        return $transactionActionToKeyMap;
    }
}