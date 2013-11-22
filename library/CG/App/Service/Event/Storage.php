<?php
namespace CG\App\Service\Event;

use CG\Stdlib\Storage\FetchInterface;
use CG\Stdlib\Storage\FetchByIdsInterface;
use CG\Stdlib\Storage\FetchAllInterface;
use CG\Stdlib\Storage\SaveInterface;
use CG\Stdlib\Storage\RemoveInterface;

interface Storage extends FetchInterface, FetchByIdsInterface, FetchAllInterface, SaveInterface, RemoveInterface
{
    public function fetchCollectionByServiceIdAndType($serviceId, $type);
    public function fetchCollectionByServiceId($serviceId);
    public function fetchCollectionByType($type);
}
