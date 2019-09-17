<?php
namespace CG\Stripe\Product;

class Plan
{
    public const CG_USAGE_TYPE_ORDERS = 'orders';

    /** @var string */
    protected $id;
    /** @var string */
    protected $productId;
    /** @var string */
    protected $nickname;
    /** @var string */
    protected $billingScheme;
    /** @var array */
    protected $metadata;
    // There are more fields but this is all we need for now

    public function __construct(string $id, string $productId, string $nickname, string $billingScheme, array $metadata)
    {
        $this->id = $id;
        $this->productId = $productId;
        $this->nickname = $nickname;
        $this->billingScheme = $billingScheme;
        $this->metadata = $metadata;
    }

    public static function fromJson(\stdClass $json): Plan
    {
        return new static(
            $json->id,
            $json->product,
            $json->nickname,
            $json->billing_scheme,
            isset($json->metadata) ? (array)$json->metadata : []
        );
    }

    public function getCgUsageType(): ?string
    {
        return $this->metadata['usage'] ?? null;
    }

    public function isCgUsageType(string $type): bool
    {
        return $this->getCgUsageType() == $type;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getProductId(): string
    {
        return $this->productId;
    }

    public function getNickname(): string
    {
        return $this->nickname;
    }

    public function getBillingScheme(): string
    {
        return $this->billingScheme;
    }
}