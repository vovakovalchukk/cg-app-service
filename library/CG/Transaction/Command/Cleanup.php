<?php
namespace CG\Transaction\Command;

use CG\Stdlib\DateTime;
use CG\Transaction\Command\Cleanup\TransactionKeyMap;
use CG\Transaction\Entity as Transaction;
use CG\Transaction\Predis\ClearStaleTransaction;
use Predis\Client as Predis;
use Predis\Collection\Iterator\Keyspace as PredisKeyspace;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Cleanup
{
    protected const DEFAULT_CHUNK_SIZE = 50;
    protected const DEFAULT_TIME_THRESHOLD = '1 month ago';

    /** @var Predis */
    protected $predis;

    public function __construct(Predis $predis)
    {
        $this->predis = $predis;
        $this->predis->getProfile()->defineCommand('clearstaletransaction', ClearStaleTransaction::class);
    }

    public function __invoke(InputInterface $input, OutputInterface $output)
    {
        $count = 0;
        $chunkSize = $this->getChunkSize($input);
        $cutoffTimestamp = $this->getCutoffTimestamp($input);
        $this->predis->clearstaletransaction(1,2,3,4);
        foreach ($this->fetchChunkedTransactionActionKeys($chunkSize) as $transactionActionKeys) {
            $batch = $this->predis->transaction();
            /** @var TransactionKeyMap $transactionKeyMap */
            foreach ($this->mapTransactionActionsToTransactionKeys($transactionActionKeys) as $transactionKeyMap) {
                $batch->clearstaletransaction(
                    $transactionKeyMap->getTransactionKey(),
                    $transactionKeyMap->getActionKey(),
                    $transactionKeyMap->getActionTimestamp(),
                    $cutoffTimestamp
                );
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

    protected function getChunkSize(InputInterface $input): int
    {
        $chunkSize = $input->getArgument('chunkSize');
        if (is_null($chunkSize)) {
            return static::DEFAULT_CHUNK_SIZE;
        }
        if (!preg_match('/^[1-9][0-9]*$/', $chunkSize)) {
            throw new InvalidArgumentException('Argument "chunkSize" should be a positive integer');
        }
        return $chunkSize;
    }

    protected function getCutoffTimestamp(InputInterface $input): int
    {
        $now = new DateTime();
        $timeThreshold = $input->getArgument('timeThreshold');
        if (is_null($timeThreshold)) {
            return (new DateTime(static::DEFAULT_TIME_THRESHOLD))->getTimestamp();
        }
        $cutoffTime = new DateTime($timeThreshold);
        if ($cutoffTime > $now) {
            throw new InvalidArgumentException('timeThreshold can\'t be in the future');
        }
        return $cutoffTime->getTimestamp();
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