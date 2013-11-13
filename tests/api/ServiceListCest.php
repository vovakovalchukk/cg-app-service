<?php
class ServiceListCest
{
    protected $newService;
    protected $validService;

    protected function fetchTestData()
    {
        $this->newService = ServicePage::getNewServiceData();
        $this->validService = ServicePage::getValidServiceData();
    }

    /**
     * @group service
     * @group collection
     * @group options
     */
    public function checkOptions(ApiGuy $I)
    {
        $this->fetchTestData();

        $I->wantTo('check the services endpoint supports the options verb and reports allowed methods');
        $url = ServiceListPage::getUrl();

        $I->checkOPTIONS($url, ServiceListPage::allowedMethods());
    }

    /**
     * @group service
     * @group collection
     * @group get
     */
    public function viewListOfServices(ApiGuy $I)
    {
        $this->fetchTestData();

        $I->wantTo('view a list of services');
        $url = ServiceListPage::getUrl();

        $I->prepareRequest();
        $I->sendGET($url);
        $I->validateResponse($url);
    }

    /**
     * @group service
     * @group collection
     * @group post
     */
    public function addNewService(ApiGuy $I)
    {
        $this->fetchTestData();

        $I->wantTo('register a new service');
        $url = ServiceListPage::getUrl();
        $serviceUrl = ServicePage::getUrl(ServicePage::NEW_ID);

        $I->dontSeeInDatabase('service', array('id' => ServicePage::NEW_ID));

        $I->prepareRequestForContent();
        $I->sendPOST($url, $this->newService);
        $I->validateResponse($serviceUrl, HTTP_STATUS_CREATED);
        $I->seeJsonResponseFieldsEquals(array_merge(array(
            'id' => ServicePage::NEW_ID
        ), $this->newService));

        $I->seeInDatabase('service', array_merge(array(
            'id' => ServicePage::NEW_ID
        ), $this->newService));
    }

    /**
     * @group service
     * @group collection
     * @group post
     * @group unprocessable
     */
    public function attemptToAddNewServiceWithInvalidData(ApiGuy $I)
    {
        $this->fetchTestData();

        $I->wantToTest('that attempting to add a service with incorrect data will fail');
        $url = ServiceListPage::getUrl();

        $I->dontSeeInDatabase('service', array('id' => ServicePage::NEW_ID));

        $invalidServiceData = $this->newService;
        $invalidServiceData['_invalid_'] = 'data';

        $I->prepareRequestForContent();
        $I->sendPOST($url, $invalidServiceData);
        $I->seeResponseCodeIs(HTTP_STATUS_UNPROCESSABLE_ENTITY);
    }

    /**
     * @group service
     * @group collection
     * @group post
     * @group unsupported
     */
    public function attemptToAddNewServiceWithUnsupportedContentType(ApiGuy $I)
    {
        $this->fetchTestData();

        $I->wantToTest('that attempting to add a service with unsupported content-type will fail');
        $url = ServiceListPage::getUrl();

        $I->dontSeeInDatabase('service', array('id' => ServicePage::NEW_ID));

        $I->prepareRequest();
        $I->sendPOST($url, $this->newService);
        $I->seeResponseCodeIs(HTTP_STATUS_UNSUPPORTED_MEDIA_TYPE);
    }

    /**
     * @group service
     * @group collection
     * @group post
     * @group conflict
     */
    public function attemptToReAddService(ApiGuy $I)
    {
        $this->fetchTestData();

        $I->wantToTest('that attempting to re-register a service will fail');
        $url = ServiceListPage::getUrl();

        $I->seeInDatabase('service', array('id' => ServicePage::VALID_ID));

        $I->prepareRequestForContent();
        $I->sendPOST($url, $this->validService);
        $I->seeResponseCodeIs(HTTP_STATUS_CONFLICT);
    }

    /**
     * @group service
     * @group collection
     * @group put
     * @group delete
     * @group notAllowed
     */
    public function checkNotAllowedMethods(ApiGuy $I)
    {
        $this->fetchTestData();

        $I->wantToTest('that all not allowed methods return the correct status');
        $url = ServiceListPage::getUrl();

        foreach (ServiceListPage::notAllowedMethods() as $method) {
            $method = 'send' . strtoupper($method);

            $I->prepareRequest();
            $I->{$method}($url);
            $I->seeResponseCodeIs(HTTP_STATUS_METHOD_NOT_ALLOWED);
        }
    }
}