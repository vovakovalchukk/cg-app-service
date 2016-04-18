<?php
namespace CG\Slim\Versioning\ListingStatusHistoryEntity;

use CG\Listing\StatusHistory\Entity as ListingStatusHistory;
use CG\Listing\StatusHistory\Service as ListingStatusHistoryService;
use CG\Slim\Versioning\VersioniserInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LogTrait;
use Nocarrier\Hal;

class Versioniser1 implements VersioniserInterface
{
    /** @var ListingStatusHistoryService $listingStatusHistoryService */
    protected $listingStatusHistoryService;

    public function __construct(ListingStatusHistoryService $listingStatusHistoryService)
    {
        $this->setListingStatusHistoryService($listingStatusHistoryService);
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();
        if (!isset($data['id']) || isset($data['code'])) {
            return;
        }

        try {
            /** @var ListingStatusHistory $statusHistory */
            $statusHistory = $this->listingStatusHistoryService->fetch($data['id']);
            $data['code'] = $statusHistory->getCode();
            $request->setData($data);
        } catch (NotFound $exception) {
            // New Entity - nothing to update
        }
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['code']);
        $response->setData($data);
    }

    /**
     * @return self
     */
    protected function setListingStatusHistoryService(ListingStatusHistoryService $listingStatusHistoryService)
    {
        $this->listingStatusHistoryService = $listingStatusHistoryService;
        return $this;
    }
} 
