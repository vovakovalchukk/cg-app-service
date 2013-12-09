<?php
namespace CG\App\Service;

use CG\Stdlib\Storage\FetchInterface;
use CG\Stdlib\Storage\SaveInterface;
use CG\Stdlib\Storage\RemoveInterface;

interface StorageInterface extends FetchInterface, SaveInterface, RemoveInterface
{
    public function fetchCollectionWithPagination($limit, $page);
}
