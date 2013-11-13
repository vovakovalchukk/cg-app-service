<?php
class ServiceCest
{
    protected $validService;
    protected $invalidService;

    protected function fetchTestData()
    {
        $this->validService = ServicePage::getValidServiceData();
        $this->invalidService = ServicePage::getInvalidServiceData();
    }

    /**
     * @group service
     * @group entity
     * @group options
     */
    public function checkOptions(ApiGuy $I)
    {
        $this->fetchTestData();

        $I->wantTo('check the service endpoint supports the options verb and reports allowed methods');
        $url = ServicePage::getUrl(ServicePage::VALID_ID);

        $I->checkOPTIONS($url, ServicePage::allowedMethods());
    }

    /**
     * @group service
     * @group entity
     * @group get
     */
    public function viewService(ApiGuy $I)
    {
        $this->fetchTestData();

        $I->wantTo('view a registered service');
        $url = ServicePage::getUrl(ServicePage::VALID_ID);

        $I->seeInDatabase('service', array('id' => ServicePage::VALID_ID));

        $I->prepareRequest();
        $I->sendGET($url);
        $I->validateResponse($url);
        $I->seeJsonResponseFieldsEquals($this->validService);
    }

    /**
     * @group service
     * @group entity
     * @group get
     * @group notFound
     */
    public function notViewInvalidService(ApiGuy $I)
    {
        $this->fetchTestData();

        $I->wantToTest('I can not see an invalid service');
        $url = ServicePage::getUrl(ServicePage::INVALID_ID);

        $I->dontSeeInDatabase('service', array('id' => ServicePage::INVALID_ID));

        $I->prepareRequest();
        $I->sendGET($url);
        $I->seeResponseCodeIs(HTTP_STATUS_NOT_FOUND);
    }

    /**
     * @group service
     * @group entity
     * @group put
     */
    public function updateService(ApiGuy $I)
    {
        $this->fetchTestData();

        $I->wantTo('update a registered service');
        $url = ServicePage::getUrl(ServicePage::VALID_ID);

        $I->seeInDatabase('service', $this->validService);

        $updatedService = $this->validService;
        $updatedService['type'] = 'updated';
        $updatedService['endpoint'] = 'http://example.org';

        $I->prepareRequestForContent();
        $I->sendPUT($url, $updatedService);
        $I->validateResponse($url);
        $I->seeJsonResponseFieldsEquals($updatedService);

        $I->seeInDatabase('service', $updatedService);
    }

    /**
     * @group service
     * @group entity
     * @group put
     * @group unprocessable
     */
    public function notUpdateServiceWithInvalidData(ApiGuy $I)
    {
        $this->fetchTestData();

        $I->wantToTest('I can not update a registered service with invalid data');
        $url = ServicePage::getUrl(ServicePage::VALID_ID);

        $I->seeInDatabase('service', $this->validService);

        $invalidServiceData = $this->validService;
        $invalidServiceData['type'] = 'updated';
        $invalidServiceData['endpoint'] = 'http://example.org';
        $invalidServiceData['_invalid_'] = 'data';

        $I->prepareRequestForContent();
        $I->sendPUT($url, $invalidServiceData);
        $I->seeResponseCodeIs(HTTP_STATUS_UNPROCESSABLE_ENTITY);
    }

    /**
     * @group service
     * @group entity
     * @group put
     * @group unsupported
     */
    public function notUpdateServiceWithUnsupportedContentType(ApiGuy $I)
    {
        $this->fetchTestData();

        $I->wantToTest('I can not update a registered service with unsupported content-type');
        $url = ServicePage::getUrl(ServicePage::VALID_ID);

        $I->seeInDatabase('service', $this->validService);

        $updatedService = $this->validService;
        $updatedService['type'] = 'updated';
        $updatedService['endpoint'] = 'http://example.org';

        $I->prepareRequest();
        $I->sendPUT($url, $updatedService);
        $I->seeResponseCodeIs(HTTP_STATUS_UNSUPPORTED_MEDIA_TYPE);
    }

    /**
     * @group service
     * @group entity
     * @group put
     * @group notFound
     */
    public function notUpdateInvalidService(ApiGuy $I)
    {
        $this->fetchTestData();

        $I->wantToTest('I can not update an invalid service');
        $url = ServicePage::getUrl(ServicePage::INVALID_ID);

        $I->dontSeeInDatabase('service', array('id' => ServicePage::INVALID_ID));

        $I->prepareRequestForContent();
        $I->sendPUT($url, $this->invalidService);
        $I->seeResponseCodeIs(HTTP_STATUS_NOT_FOUND);
    }

    /**
     * @group service
     * @group entity
     * @group delete
     */
    public function deleteService(ApiGuy $I)
    {
        $this->fetchTestData();

        $I->wantTo('delete a registered service');
        $url = ServicePage::getUrl(ServicePage::VALID_ID);

        $I->seeInDatabase('service', array('id' => ServicePage::VALID_ID));

        $I->prepareRequest();
        $I->sendDELETE($url);
        $I->seeResponseCodeIs(HTTP_STATUS_NO_CONTENT);

        $I->dontSeeInDatabase('service', array('id' => ServicePage::VALID_ID));
    }

    /**
     * @group service
     * @group entity
     * @group delete
     * @group notFound
     */
    public function notDeleteInvalidService(ApiGuy $I)
    {
        $this->fetchTestData();

        $I->wantToTest('I can not delete an invalid service');
        $url = ServicePage::getUrl(ServicePage::INVALID_ID);

        $I->dontSeeInDatabase('service', array('id' => ServicePage::INVALID_ID));

        $I->prepareRequest();
        $I->sendDELETE($url);
        $I->seeResponseCodeIs(HTTP_STATUS_NOT_FOUND);
    }

    /**
     * @group service
     * @group entity
     * @group post
     * @group notAllowed
     */
    public function checkNotAllowedMethods(ApiGuy $I)
    {
        $this->fetchTestData();

        $I->wantToTest('that all not allowed methods return the correct status');
        $url = ServicePage::getUrl(ServicePage::VALID_ID);

        foreach (ServicePage::notAllowedMethods() as $method) {
            $method = 'send' . strtoupper($method);

            $I->prepareRequest();
            $I->{$method}($url);
            $I->seeResponseCodeIs(HTTP_STATUS_METHOD_NOT_ALLOWED);
        }
    }
}