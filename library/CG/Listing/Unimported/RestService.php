<?php
namespace CG\Listing\Unimported;

use CG\Image\Filter as ImageFilter;
use CG\Image\Service as ImageStorage;
use CG\Listing\Unimported\Collection as UnimportedListingCollection;
use CG\Listing\Unimported\Entity as UnimportedListingEntity;
use CG\Listing\Unimported\Filter;
use CG\Listing\Unimported\Marketplace\Service as MarketplaceService;
use CG\Listing\Unimported\ServiceAbstract;
use CG\Slim\Patch\ServiceTrait as PatchServiceTrait;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\ServiceTrait;
use Zend\EventManager\GlobalEventManager;

class RestService extends ServiceAbstract
{
    use ServiceTrait {
        save as saveTrait;
    }
    use PatchServiceTrait;

    const DEFAULT_LIMIT = 10;
    const DEFAULT_PAGE = 1;
    const ALL_PAGE = 'all';

    /** @var ImageStorage */
    protected $imageStorage;
    /** @Var MarketplaceService */
    protected $marketplaceService;
    /** @var GlobalEventManager */
    protected $globalEventManager;

    public function __construct(
        StorageInterface $repository,
        Mapper $mapper,
        ImageStorage $imageStorage,
        MarketplaceService $marketplaceService,
        GlobalEventManager $globalEventManager
    ) {
        parent::__construct($repository, $mapper);
        $this->imageStorage = $imageStorage;
        $this->marketplaceService = $marketplaceService;
        $this->globalEventManager = $globalEventManager;
    }

    public function fetchAsHal($id)
    {
        $entity = $this->fetch($id);
        //Converting to Collection removes need for duplicate code throughout the codebase
        $unimportedListingCollection = new UnimportedListingCollection(UnimportedListingEntity::class, __FUNCTION__, compact('id'));
        $unimportedListingCollection->attach($entity);
        $this->fetchCollectionEmbeds($unimportedListingCollection);
        return $this->mapper->toHal($entity);
    }

    public function fetchCollectionByFilterAsHal(Filter $filter)
    {
        if (!$filter->getLimit()) {
            $filter->setLimit(static::DEFAULT_LIMIT);
        }
        if (!$filter->getPage()) {
            $filter->setPage(static::DEFAULT_PAGE);
        }

        $collection = $this->fetchCollectionByFilter($filter);
        $collection = $this->fetchCollectionEmbeds($collection);
        return $this->mapper->collectionToHal(
            $collection, "/unimportedListing", $filter->getLimit(), $filter->getPage(), $filter->toArray()
        );
    }

    protected function fetchCollectionEmbeds(UnimportedListingCollection $unimportedListingCollection)
    {
        $imageIds = [];
        foreach($unimportedListingCollection as $unimportedListing) {
            $imageId = $unimportedListing->getImageId();
            if (is_null($imageId)) {
                continue;
            }
            $imageIds[] = $imageId;
        }

        try {
            if (empty($imageIds)) {
                throw new NotFound();
            }
            $imageIds = array_unique($imageIds);
            $imageFilter = new ImageFilter();
            $imageFilter->setId($imageIds)
                ->setPage(static::DEFAULT_PAGE)
                ->setLimit(static::ALL_PAGE);
            $images = $this->imageStorage->fetchCollectionByPaginationAndFilters($imageFilter);
        } catch (NotFound $e) {
            // Image not found
        }

        foreach($unimportedListingCollection as $unimportedListing) {
            if(isset($images) && $images->getById($unimportedListing->getImageId())) {
                $unimportedListing->setImage($images->getById($unimportedListing->getImageId()));
            }
        }

        return $unimportedListingCollection;
    }

    public function save($entity)
    {
        $this->checkUnimportedListingStatus($entity);
        $hal = $this->saveTrait($entity);
        try {
            $this->marketplaceService->saveFromUnimportedListing($entity);
        } catch (\Exception $exception) {
            // Saving marketplace links is a secondary concern to saving the unimported listing so just ignore any errors
            $this->logException($exception, 'debug', __NAMESPACE__, ['note' => 'Failed to save marketplace link']);
        }
        return $hal;
    }

    protected function checkUnimportedListingStatus(UnimportedListingEntity $entity)
    {
        if (!$this->checkUnimportedListingSkusAreValid($entity)) {
            $this->logDebug('Unexpected chars detected in one or more SKUs for UnimportedListing with externalId %s for account %d. Changing status.', [$entity->getExternalId(), $entity->getAccountId()], static::LOG_CODE, ['ou' => $entity->getOrganisationUnitId(), 'account' => $entity->getAccountId()]);
            $entity->setStatus(Status::UNEXPECTED_CHARS_IN_SKU);
            return;
        }
    }

    protected function checkUnimportedListingSkusAreValid(UnimportedListingEntity $entity)
    {
        if ($entity->getVariationCount() == 0) {
            return ($entity->getSku() == '' || $this->checkSkuIsValid($entity->getSku()));
        }
        foreach ($entity->getVariationSkus() as $sku) {
            if (!$this->checkSkuIsValid($sku)) {
                return false;
            }
        }
        return true;
    }

    protected function checkSkuIsValid($sku)
    {
        // ASCII chars only, no leading / trailing whitespace
        return (preg_match('/^[[:ascii:]]+$/', $sku) && !preg_match('/(^\s|\s$)/', $sku));
    }

    // To satisfy \CG\Slim\Patch\ServiceTrait
    protected function getEventManager()
    {
        return $this->globalEventManager;
    }
}