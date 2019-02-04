<?php
namespace CG\Product\Link\Storage;

use Zend\Db\Sql\Where;
use function CG\Stdlib\escapeLikeValue;

trait DbLinkIdTrait
{
    protected function getLinkIdWhere(?string $table, ...$ouIdProductSkus): Where
    {
        $where = new Where(null, Where::COMBINED_BY_OR);
        $prefix = $table ? sprintf('%s.', $table) : '';
        foreach ($ouIdProductSkus as $ouIdProductSku) {
            [$organisationUnitId, $productSku] = array_pad(explode('-', $ouIdProductSku, 2), 2, '');
            $where->addPredicate(
                (new Where())
                    ->equalTo($prefix . 'organisationUnitId', $organisationUnitId)
                    ->like($prefix . 'sku', escapeLikeValue($productSku))
            );
        }
        return $where;
    }
}