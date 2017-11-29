<?php
namespace CG\TestAsset\Product\LinkLeaf;

use CG\Product\LinkLeaf\StorageInterface as BaseStorage;
use CG\Stdlib\Storage\RemoveInterface;
use CG\Stdlib\Storage\SaveInterface;

interface StorageInterface extends BaseStorage, SaveInterface, RemoveInterface
{

}