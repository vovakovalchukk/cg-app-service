<?php
class ServiceEventListCest
{
    protected $newEvent;
    protected $validEvent;

    protected function fetchTestData()
    {
        $this->newEvent = ServiceEventPage::getNewEventData();
        $this->validEvent = ServiceEventPage::getValidEventData();
    }

    /**
     * @group service
     * @group event
     * @group collection
     * @group options
     */
    public function checkOptions(ApiGuy $I)
    {
        $this->fetchTestData();

        $I->wantTo('check the service events endpoint supports the options verb and reports allowed methods');
        $url = ServiceEventListPage::getUrl(ServicePage::VALID_ID);

        $I->checkOPTIONS($url, ServiceEventListPage::allowedMethods());
    }

    /**
     * @group service
     * @group event
     * @group collection
     * @group get
     */
    public function viewListOfServiceEvents(ApiGuy $I)
    {
        $this->fetchTestData();

        $I->wantTo('view a list of service events');
        $url = ServiceEventListPage::getUrl(ServicePage::VALID_ID);

        $I->seeInDatabase('service', array('id' => ServicePage::VALID_ID));

        $I->prepareRequest();
        $I->sendGET($url);
        $I->validateResponse($url);
    }

    /**
     * @group service
     * @group event
     * @group collection
     * @group get
     * @group notFound
     */
    public function notViewEventsForInvalidService(ApiGuy $I)
    {
        $this->fetchTestData();

        $I->wantToTest('I can not view events on an invalid service');
        $url = ServiceEventListPage::getUrl(ServicePage::INVALID_ID);

        $I->dontSeeInDatabase('service', array('id' => ServicePage::INVALID_ID));

        $I->prepareRequest();
        $I->sendGET($url);
        $I->seeResponseCodeIs(HTTP_STATUS_NOT_FOUND);
    }

    /**
     * @group service
     * @group event
     * @group collection
     * @group post
     */
    public function addNewEventToValidService(ApiGuy $I)
    {
        $this->fetchTestData();

        $I->wantTo('add a new event to a registered service');
        $url = ServiceEventListPage::getUrl(ServicePage::VALID_ID);
        $eventUrl = ServiceEventPage::getUrl(ServicePage::VALID_ID, ServiceEventPage::NEW_TYPE);

        $I->dontSeeInDatabase('service_event', array(
            'service_id' => ServicePage::VALID_ID,
            'type' => ServiceEventPage::NEW_TYPE
        ));

        $I->prepareRequestForContent();
        $I->sendPOST($url, $this->newEvent);
        $I->validateResponse($eventUrl, HTTP_STATUS_CREATED);
        $I->seeJsonResponseFieldsEquals(array_merge(array(
            'type' => ServiceEventPage::NEW_TYPE
        ), $this->newEvent));

        $I->seeInDatabase('service_event', array_merge(array(
            'service_id' => ServicePage::VALID_ID,
            'type' => ServiceEventPage::NEW_TYPE
        ), $this->newEvent));
    }

    /**
     * @group service
     * @group event
     * @group collection
     * @group post
     * @group unprocessable
     */
    public function attemptToAddNewEventToValidServiceWithInvalidData(ApiGuy $I)
    {
        $this->fetchTestData();

        $I->wantToTest('I can not add a new event to a registered service with invalid data');
        $url = ServiceEventListPage::getUrl(ServicePage::VALID_ID);

        $I->dontSeeInDatabase('service_event', array(
            'service_id' => ServicePage::VALID_ID,
            'type' => ServiceEventPage::NEW_TYPE
        ));

        $invalidEventData = $this->newEvent;
        $invalidEventData['_invalid_'] = 'data';

        $I->prepareRequestForContent();
        $I->sendPOST($url, $invalidEventData);
        $I->seeResponseCodeIs(HTTP_STATUS_UNPROCESSABLE_ENTITY);
    }

    /**
     * @group service
     * @group event
     * @group collection
     * @group post
     * @group unsupported
     */
    public function attemptToAddNewEventToValidServiceWithUnsupportedContentType(ApiGuy $I)
    {
        $this->fetchTestData();

        $I->wantToTest('I can not add a new event to a registered service with unsupported content-type');
        $url = ServiceEventListPage::getUrl(ServicePage::VALID_ID);

        $I->dontSeeInDatabase('service_event', array(
            'service_id' => ServicePage::VALID_ID,
            'type' => ServiceEventPage::NEW_TYPE
        ));

        $I->prepareRequest();
        $I->sendPOST($url, $this->newEvent);
        $I->seeResponseCodeIs(HTTP_STATUS_UNSUPPORTED_MEDIA_TYPE);
    }

    /**
     * @group service
     * @group event
     * @group collection
     * @group post
     * @group conflict
     */
    public function attemptToReAddEventToValidService(ApiGuy $I)
    {
        $this->fetchTestData();

        $I->wantToTest('that attempting to re-register a service event will fail');
        $url = ServiceEventListPage::getUrl(ServicePage::VALID_ID);

        $I->seeInDatabase('service_event', array(
            'service_id' => ServicePage::VALID_ID,
            'type' => ServiceEventPage::VALID_TYPE
        ));

        $I->prepareRequestForContent();
        $I->sendPOST($url, $this->validEvent);
        $I->seeResponseCodeIs(HTTP_STATUS_CONFLICT);
    }

    /**
     * @group service
     * @group event
     * @group collection
     * @group post
     * @group notFound
     */
    public function attemptToAddNewEventToInvalidService(ApiGuy $I)
    {
        $this->fetchTestData();

        $I->wantToTest('that adding a new event to an invalid service will fail');
        $url = ServiceEventListPage::getUrl(ServicePage::INVALID_ID);

        $I->dontSeeInDatabase('service', array('id' => ServicePage::INVALID_ID));

        $I->prepareRequestForContent();
        $I->sendPOST($url, $this->newEvent);
        $I->seeResponseCodeIs(HTTP_STATUS_NOT_FOUND);
    }

    /**
     * @group service
     * @group event
     * @group collection
     * @group put
     * @group delete
     * @group notAllowed
     */
    public function checkNotAllowedMethods(ApiGuy $I)
    {
        $this->fetchTestData();

        $I->wantToTest('that all not allowed methods return the correct status');
        $url = ServiceEventListPage::getUrl(ServicePage::VALID_ID);

        foreach (ServiceEventListPage::notAllowedMethods() as $method) {
            $method = 'send' . strtoupper($method);

            $I->prepareRequest();
            $I->{$method}($url);
            $I->seeResponseCodeIs(HTTP_STATUS_METHOD_NOT_ALLOWED);
        }
    }
}