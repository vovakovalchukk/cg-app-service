<?php
namespace CG\Order\Test\Api\Cest;

use CG\Codeception\Cest\Rest\EntityTrait;
use CG\Order\Test\Api\Page\OrderEntityPage;
use CG\Codeception\Cest\Rest\EntityETagTrait;
use ApiGuy;
use CG\Http\StatusCode as HttpStatus;

class OrderEntityCest
{
    use EntityTrait, EntityETagTrait;

    protected function getPageClass()
    {
        return OrderEntityPage::class;
    }

    public function checkDeletingLastOrderWithTagDeletesTag(ApiGuy $I)
    {
        $page = $this->getPageClass();
        if (!isset($page::allowedMethods()[$page::DELETE])) {
            $I->amGoingTo('skip checking deleting the last order with a tag deletes the tag as DELETE is not an allowed method');
            return;
        }

        $I->wantTo('check deleting the last order with a tag deletes the tag from the orderTag endpoint');

        $I->prepareRequest();
        $I->sendGET($page::getPrimaryEntityUrl());
        $I->seeResponseCodeIs(HttpStatus::OK);

        $I->prepareRequest();
        $I->sendDELETE($page::getPrimaryEntityUrl());
        $I->seeResponseCodeIs(HttpStatus::NO_CONTENT);

        $I->prepareRequest();
        $I->sendGET($page::getDeletedTagUrl());
        $I->seeResponseCodeIs(HttpStatus::OK);

        $I->prepareRequest();
        $I->sendGET($page::getSecondaryEntityUrl());
        $I->seeResponseCodeIs(HttpStatus::OK);

        $I->prepareRequest();
        $I->sendDELETE($page::getSecondaryEntityUrl());
        $I->seeResponseCodeIs(HttpStatus::NO_CONTENT);

        $I->prepareRequest();
        $I->sendGET($page::getDeletedTagUrl());
        $I->seeResponseCodeIs(HttpStatus::NOT_FOUND);
    }

    public function checkUpdatingOrderWithNewTagAddsTag(ApiGuy $I)
    {
        $page = $this->getPageClass();
        if (!isset($page::allowedMethods()[$page::PUT])) {
            $I->amGoingTo('skip checking updating an order with a new tag adds the tag as PUT is not an allowed method');
            return;
        }

        $I->wantTo('checking updating an order with a new tag adds the tag');

        $I->prepareRequest();
        $I->sendGET($page::getUpdatedTagUrl());
        $I->seeResponseCodeIs(HttpStatus::NOT_FOUND);

        $I->prepareRequest();
        $I->sendGET($page::getPrimaryEntityUrl());
        $I->seeResponseCodeIs(HttpStatus::OK);

        $updatedEntity = $page::getPrimaryTestEntity();

        $updatedEntity['tag'][] = 'tag6';

        $I->prepareRequest();
        $I->sendPUT($page::getPrimaryEntityUrl(), $updatedEntity);
        $I->seeResponseCodeIs(HttpStatus::OK);

        $I->prepareRequest();
        $I->sendGET($page::getUpdatedTagUrl());
        $I->seeResponseCodeIs(HttpStatus::OK);
    }
}