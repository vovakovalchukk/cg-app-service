<?php
namespace CG\Product\Link\Storage;

use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use function CG\Stdlib\escapeLikeValue;

trait DbMaxOrderSelectTrait
{
    protected function getMaxOrderSelect(Sql $readSql, ...$ouIdProductSkus): Select
    {
        $ouIds = [];
        foreach ($ouIdProductSkus as $ouIdProductSku) {
            [$organisationUnitId, ] = array_pad(explode('-', $ouIdProductSku, 2), 2, '');
            $ouIds[$organisationUnitId] = $organisationUnitId;
        }

        $select = $readSql
            ->select('productLinkPath')
            ->columns(['pathId', 'order' => new Expression('MAX(?)', ['order'], [Expression::TYPE_IDENTIFIER])])
            ->join('productLink', 'productLink.linkId = productLinkPath.linkId', []);

        if (!empty($ouIds)) {
            $select->where->in('productLink.organisationUnitId', array_values($ouIds));
        }

        $select->group(['pathId']);
        return $select;
    }
}