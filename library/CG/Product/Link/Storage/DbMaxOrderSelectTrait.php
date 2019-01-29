<?php
namespace CG\Product\Link\Storage;

use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;

trait DbMaxOrderSelectTrait
{
    protected function getMaxOrderSelect(Sql $readSql): Select
    {
        return $readSql
            ->select('productLinkPath')
            ->columns(['pathId', 'order' => new Expression('MAX(?)', ['order'], [Expression::TYPE_IDENTIFIER])])
            ->group(['pathId']);
    }
}