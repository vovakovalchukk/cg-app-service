<?php
namespace CG\Order\Service\Label\Storage;

use CG\Order\Shared\Label\Collection;
use CG\Order\Shared\Label\Filter;
use CG\Stdlib\Storage\FetchInterface;
use CG\Stdlib\Storage\SaveInterface;
use CG\Stdlib\Storage\RemoveInterface;

interface MetaDataInterface extends FetchInterface, SaveInterface, RemoveInterface
{
    public function fetchCollectionByFilter(Filter $filter): Collection;
}