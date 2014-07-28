<?php
namespace CG\Order\Test\Api\Cest;

use CG\Codeception\Cest\Rest\EntityTrait;
use CG\Order\Test\Api\Page\UserPreferenceEntityPage;
use CG\Codeception\Cest\Rest\EntityETagTrait;

class UserPreferenceCest
{
    use EntityTrait;

    protected function getPageClass()
    {
        return UserPreferenceEntityPage::class;
    }
}