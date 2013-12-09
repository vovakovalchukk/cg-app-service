<?php
namespace CG\App\Service\Event;

use CG\Stdlib\Storage\FetchInterface;
use CG\Stdlib\Storage\SaveInterface;
use CG\Stdlib\Storage\RemoveInterface;

interface StorageInterface extends FetchInterface, SaveInterface, RemoveInterface
{
    public function fetchCollectionByServiceId($limit, $page, $serviceId);
    public function fetchCollectionByServiceIds(array $serviceIds);
}
