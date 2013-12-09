<?php
namespace CG\Order\Test\Api\Cest\Service;

use CG\Codeception\Cest\Rest\EntityTrait;
use CG\Order\Test\Api\Page\Service\SubscribedEventEntityPage;
use CG\Codeception\Cest\Rest\EntityETagTrait;
use ApiGuy;

class SubscribedEventEntityCest
{
    use EntityTrait, EntityETagTrait;

    protected function getPageClass()
    {
        return SubscribedEventEntityPage::class;
    }

    public function viewSecondaryEntity(ApiGuy $I)
    {
        $page = $this->getPageClass();
        if (!isset($page::allowedMethods()[$page::GET])) {
            $I->amGoingTo('skip viewing secondary entity as GET is not an allowed method');
            return;
        }

        $I->wantTo('view the secondary entity');

        $I->prepareRequest();
        $I->sendGET($page::getSecondaryEntityUrl());
        $I->seeJsonResponseFieldsEquals($page::getTestCollection()[1]);
    }
}