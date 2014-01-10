<?php
namespace CG\Order\Test\Api\Cest;

use CG\Order\Test\Api\Page\UserPreferencePage;
use CG\Codeception\Cest\Rest\CollectionTrait;

class UserPreference
{
    use CollectionTrait;

    protected function getPageClass()
    {
        return UserPreferencePage::class;
    }
}