<?php
namespace CG\Stripe;

use CG\Stripe\Subscription\Item;

class Subscription
{
    /** @var string */
    protected $id;
    /** @var string */
    protected $customer;
    /** @var int */
    protected $created;
    /** @var int|null */
    protected $cancelledAt;
    /** @var int|null */
    protected $endedAt;
    /** @var Item[] */
    protected $items;
    // There are more fields than this but this is all we currently need

    public function __construct(
        string $id,
        string $customer,
        int $created,
        ?int $cancelledAt,
        ?int $endedAt,
        array $items
    ) {
        $this->id = $id;
        $this->customer = $customer;
        $this->created = $created;
        $this->cancelledAt = $cancelledAt;
        $this->endedAt = $endedAt;
        $this->items = $items;
    }

    /**
     * @param Item[] $items
     */
    public static function fromJson(\stdClass $json, array $items): Subscription
    {
        return new static(
            $json->id,
            $json->customer,
            $json->created,
            $json->cancelledAt ?? null,
            $json->endedAt ?? null,
            $items
        );
    }

    public function getItemForCgUsageType(string $usageType): ?Item
    {
        foreach ($this->items as $item) {
            if ($item->getPlan()->isCgUsageType($usageType)) {
                return $item;
            }
        }
        return null;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getCustomer(): string
    {
        return $this->customer;
    }

    public function getCreated(): int
    {
        return $this->created;
    }

    public function getCancelledAt(): ?int
    {
        return $this->cancelledAt;
    }

    public function getEndedAt(): ?int
    {
        return $this->endedAt;
    }

    public function getItems(): array
    {
        return $this->items;
    }
}