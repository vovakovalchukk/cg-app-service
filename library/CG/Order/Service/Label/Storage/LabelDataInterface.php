<?php
namespace CG\Order\Service\Label\Storage;

use CG\Stdlib\Storage\SaveInterface;
use CG\Stdlib\Storage\RemoveInterface;

interface LabelDataInterface extends SaveInterface, RemoveInterface
{
    public function fetch(int $id, int $ouId): array;
}