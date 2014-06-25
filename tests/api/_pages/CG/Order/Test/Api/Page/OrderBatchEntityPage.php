<?php
namespace CG\Order\Test\Api\Page;

use CG\Codeception\Cest\Rest\EntityPageTrait;
use CG\Codeception\Cest\Rest\EntityPageInterface;

class OrderBatchEntityPage extends OrderBatchPage implements EntityPageInterface
{
    use EntityPageTrait;

    public static function getCollectionPage()
    {
        return OrderBatchPage::class;
    }

    public static function notAllowedMethods()
    {
        return [
            static::POST => static::POST
        ];
    }

    public static function getPrimaryUpdatedTestEntity()
    {
        $primary = static::getPrimaryTestEntity();
        $secondary = static::getSecondaryTestEntity();
        if(empty($primary) || empty($secondary) || !is_array($secondary) || !is_array($primary)){
            return false;
        }
        foreach($secondary as $key => $value){
            if ($key != 'id') {
                $primary[$key] = $value;
            }
        }
        return $primary;
    }
}