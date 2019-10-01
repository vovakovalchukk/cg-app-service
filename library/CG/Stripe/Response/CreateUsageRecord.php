<?php
namespace CG\Stripe\Response;

use CG\Stripe\ResponseInterface;
use CG\Stripe\UsageRecord;

class CreateUsageRecord implements ResponseInterface
{
    /** @var UsageRecord */
    protected $usageRecord;

    public function __construct(UsageRecord $usageRecord)
    {
        $this->usageRecord = $usageRecord;
    }

    public static function fromJson(\stdClass $json): ResponseInterface
    {
        return new static(UsageRecord::fromJson($json));
    }

    public function getUsageRecord(): UsageRecord
    {
        return $this->usageRecord;
    }
}