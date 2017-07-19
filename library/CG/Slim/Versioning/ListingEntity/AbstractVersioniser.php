<?php
namespace CG\Slim\Versioning\ListingEntity;

use CG\Listing\Client\Service;
use CG\Listing\Entity as Listing;
use CG\Slim\Versioning\VersioniserInterface;
use CG\Stdlib\Exception\Runtime\NotFound;

abstract class AbstractVersioniser implements VersioniserInterface
{
    /** @var Service $service */
    protected $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    /**
     * @return Listing
     * @throws NotFound
     */
    protected function fetchEntity(array $data)
    {
        if (!isset($data['id'])) {
            throw new NotFound('No id set in data');
        }

        return $this->service->fetch($data['id']);
    }
}
