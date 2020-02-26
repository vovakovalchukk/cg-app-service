<?php
namespace CG\Transaction\Command\Cleanup;

class TransactionKeyMap
{
    protected $transactionKey;
    protected $actionKey;
    protected $actionTimestamp;

    public function __construct(
        string $transactionKey,
        string $actionKey,
        int $actionTimestamp
    ) {
        $this->transactionKey = $transactionKey;
        $this->actionKey = $actionKey;
        $this->actionTimestamp = $actionTimestamp;
    }

    public function getTransactionKey(): string
    {
        return $this->transactionKey;
    }

    public function getActionKey(): string
    {
        return $this->actionKey;
    }

    public function getActionTimestamp(): int
    {
        return $this->actionTimestamp;
    }
}