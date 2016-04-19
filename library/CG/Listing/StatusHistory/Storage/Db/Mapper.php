<?php
namespace CG\Listing\StatusHistory\Storage\Db;

use CG\Listing\StatusHistory\Mapper as BaseMapper;
use CG\Listing\StatusHistory\Storage\Db;

class Mapper extends BaseMapper
{
    public function fromArray(array $entityData)
    {
        if (isset($entityData['code']) && !is_array($entityData['code'])) {
            $entityData['code'] = explode(Db::SEPERATOR, $entityData['code']);
        }
        return parent::fromArray($entityData);
    }
} 
