<?php
namespace CG\Order\Shared\CustomerCounts;

class NullStorage implements StorageInterface
{
    public function fetch($ouId, $customer)
    {
        return 0;
    }

    public function increment($ouId, $customer)
    {
        // TODO: Implement increment() method.
    }

    public function saveCount($ouId, $customer, $count)
    {
        // TODO: Implement saveCount() method.
    }

    public function decrement($ouId, $customer)
    {
        // TODO: Implement decrement() method.
    }

    public function remove(int $ouId, string $customer)
    {
        // TODO: Implement remove() method.
    }
}
