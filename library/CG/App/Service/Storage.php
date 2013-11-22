<?php
namespace CG\App\Service;

use CG\Stdlib\Storage\FetchInterface;
use CG\Stdlib\Storage\FetchByIdsInterface;
use CG\Stdlib\Storage\FetchAllInterface;
use CG\Stdlib\Storage\SaveInterface;
use CG\Stdlib\Storage\RemoveInterface;

interface Storage extends FetchInterface, FetchByIdsInterface, FetchAllInterface, SaveInterface, RemoveInterface
{
}
