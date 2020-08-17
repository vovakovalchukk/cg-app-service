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

        return $readSql
            ->select('productLinkPath')
            ->columns(['pathId', 'order' => new Expression('MAX(?)', ['order'], [Expression::TYPE_IDENTIFIER])])
            ->join('productLink', 'productLink.linkId = productLinkPath.linkId', [])
            ->where('productLink.organisationUnitId', array_values($ouIds))
            ->group(['pathId']);
    }
}