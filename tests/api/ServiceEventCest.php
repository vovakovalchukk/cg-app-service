<?php
class ServiceEventCest
{
    protected $validEvent;
    protected $invalidEvent;

    protected function fetchTestData()
    {
        $this->validEvent = ServiceEventPage::getValidEventData();
        $this->invalidEvent = ServiceEventPage::getInvalidEventData();
    }

    /**
     * @group service
     * @group event
     * @group entity
     * @group options
     */
    public function checkOptions(ApiGuy $I)
    {
        $this->fetchTestData();

        $I->wantTo('check the service event endpoint supports the options verb and reports allowed methods');
        $url = ServiceEventPage::getUrl(ServicePage::VALID_ID, ServiceEventPage::VALID_TYPE);

        $I->checkOPTIONS($url, ServiceEventPage::allowedMethods());
    }

    /**
     * @group service
     * @group event
     * @group entity
     * @group get
     */
    public function viewServiceEvent(ApiGuy $I)
    {
        $this->fetchTestData();

        $I->wantTo('view a registered services event');
        $url = ServiceEventPage::getUrl(ServicePage::VALID_ID, ServiceEventPage::VALID_TYPE);

        $I->seeInDatabase('service_event', array(
            'service_id' => ServicePage::VALID_ID,
            'type' => ServiceEventPage::VALID_TYPE
        ));

        $I->prepareRequest();
        $I->sendGET($url);
        $I->validateResponse($url);
        $I->seeJsonResponseFieldsEquals($this->validEvent);
    }

    /**
     * @group service
     * @group event
     * @group entity
     * @group get
     * @group notFound
     */
    public function notViewServiceInvalidEvent(ApiGuy $I)
    {
        $this->fetchTestData();

        $I->wantToTest('I can not view a invalid event on a registered service');
        $url = ServiceEventPage::getUrl(ServicePage::VALID_ID, ServiceEventPage::INVALID_TYPE);

        $I->dontSeeInDatabase('service_event', array(
            'service_id' => ServicePage::VALID_ID,
            'type' => ServiceEventPage::INVALID_TYPE
        ));

        $I->prepareRequest();
        $I->sendGET($url);
        $I->seeResponseCodeIs(HTTP_STATUS_NOT_FOUND);
    }

    /**
     * @group service
     * @group event
     * @group entity
     * @group get
     * @group notFound
     */
    public function notViewInvalidServiceEvent(ApiGuy $I)
    {
        $this->fetchTestData();

        $I->wantToTest('I can not view a event on an invalid services event');
        $url = ServiceEventPage::getUrl(ServicePage::INVALID_ID, ServiceEventPage::VALID_TYPE);

        $I->dontSeeInDatabase('service', array('id' => ServicePage::INVALID_ID));

        $I->prepareRequest();
        $I->sendGET($url);
        $I->seeResponseCodeIs(HTTP_STATUS_NOT_FOUND);
    }

    /**
     * @group service
     * @group event
     * @group entity
     * @group put
     */
    public function updateEventOnValidService(ApiGuy $I)
    {
        $this->fetchTestData();

        $I->wantTo('update a event on a registered service');
        $url = ServiceEventPage::getUrl(ServicePage::VALID_ID, ServiceEventPage::VALID_TYPE);

        $I->seeInDatabase('service_event', array_merge(array(
            'service_id' => ServicePage::VALID_ID,
            'type' => ServiceEventPage::VALID_TYPE
        ), $this->validEvent));

        $updatedEvent = $this->validEvent;
        $updatedEvent['instances'] = 2;
        $updatedEvent['endpoint'] = 'http://example.org';

        $I->prepareRequestForContent();
        $I->sendPUT($url, $updatedEvent);
        $I->validateResponse($url);
        $I->seeJsonResponseFieldsEquals($updatedEvent);

        $I->seeInDatabase('service_event', array_merge(array(
            'service_id' => ServicePage::VALID_ID,
            'type' => ServiceEventPage::VALID_TYPE
        ), $updatedEvent));
    }

    /**
     * @group service
     * @group event
     * @group entity
     * @group put
     * @group unprocessable
     */
    public function notUpdateEventOnValidServiceWithInvalidData(ApiGuy $I)
    {
        $this->fetchTestData();

        $I->wantToTest('I can not update a event on a registered service with invalid data');
        $url = ServiceEventPage::getUrl(ServicePage::VALID_ID, ServiceEventPage::VALID_TYPE);

        $I->seeInDatabase('service_event', array_merge(array(
            'service_id' => ServicePage::VALID_ID,
            'type' => ServiceEventPage::VALID_TYPE
        ), $this->validEvent));

        $invalidEventData = $this->validEvent;
        $invalidEventData['instances'] = 2;
        $invalidEventData['endpoint'] = 'http://example.org';
        $invalidEventData['_invalid_'] = 'data';

        $I->prepareRequestForContent();
        $I->sendPUT($url, $invalidEventData);
        $I->seeResponseCodeIs(HTTP_STATUS_UNPROCESSABLE_ENTITY);
    }

    /**
     * @group service
     * @group event
     * @group entity
     * @group put
     * @group unsupported
     */
    public function notUpdateEventOnValidServiceWithUnsupportedContentType(ApiGuy $I)
    {
        $this->fetchTestData();

        $I->wantToTest('I can not update a event on a registered service with unsupported content-type');
        $url = ServiceEventPage::getUrl(ServicePage::VALID_ID, ServiceEventPage::VALID_TYPE);

        $I->seeInDatabase('service_event', array_merge(array(
            'service_id' => ServicePage::VALID_ID,
            'type' => ServiceEventPage::VALID_TYPE
        ), $this->validEvent));

        $updatedEvent = $this->validEvent;
        $updatedEvent['instances'] = 2;
        $updatedEvent['endpoint'] = 'http://example.org';

        $I->prepareRequest();
        $I->sendPUT($url, $updatedEvent);
        $I->seeResponseCodeIs(HTTP_STATUS_UNSUPPORTED_MEDIA_TYPE);
    }

    /**
     * @group service
     * @group event
     * @group entity
     * @group put
     * @group notFound
     */
    public function notUpdateInvalidEventOnValidService(ApiGuy $I)
    {
        $this->fetchTestData();

        $I->wantToTest('I can not update an invalid event on a registered service');
        $url = ServiceEventPage::getUrl(ServicePage::VALID_ID, ServiceEventPage::INVALID_TYPE);

        $I->dontSeeInDatabase('service_event', array(
            'service_id' => ServicePage::VALID_ID,
            'type' => ServiceEventPage::INVALID_TYPE
        ));

        $I->prepareRequestForContent();
        $I->sendPUT($url, $this->invalidEvent);
        $I->seeResponseCodeIs(HTTP_STATUS_NOT_FOUND);
    }

    /**
     * @group service
     * @group event
     * @group entity
     * @group put
     * @group notFound
     */
    public function notUpdateEventOnInvalidService(ApiGuy $I)
    {
        $this->fetchTestData();

        $I->wantToTest('I can not update a event on an invalid service');
        $url = ServiceEventPage::getUrl(ServicePage::INVALID_ID, ServiceEventPage::VALID_TYPE);

        $I->dontSeeInDatabase('service', array('id' => ServicePage::INVALID_ID));

        $I->prepareRequestForContent();
        $I->sendPUT($url, $this->validEvent);
        $I->seeResponseCodeIs(HTTP_STATUS_NOT_FOUND);
    }

    /**
     * @group service
     * @group event
     * @group entity
     * @group delete
     */
    public function deleteEventOnValidService(ApiGuy $I)
    {
        $this->fetchTestData();

        $I->wantTo('delete a event on a registered service');
        $url = ServiceEventPage::getUrl(ServicePage::VALID_ID, ServiceEventPage::VALID_TYPE);

        $I->seeInDatabase('service_event', array(
            'service_id' => ServicePage::VALID_ID,
            'type' => ServiceEventPage::VALID_TYPE
        ));

        $I->prepareRequest();
        $I->sendDELETE($url);
        $I->seeResponseCodeIs(HTTP_STATUS_NO_CONTENT);

        $I->dontSeeInDatabase('service_event', array(
            'service_id' => ServicePage::VALID_ID,
            'type' => ServiceEventPage::VALID_TYPE
        ));
    }

    /**
     * @group service
     * @group event
     * @group entity
     * @group delete
     * @group notFound
     */
    public function notDeleteInvalidEventOnValidService(ApiGuy $I)
    {
        $this->fetchTestData();

        $I->wantToTest('I can not delete an invalid event on a registered service');
        $url = ServiceEventPage::getUrl(ServicePage::VALID_ID, ServiceEventPage::INVALID_TYPE);

        $I->dontSeeInDatabase('service_event', array(
            'service_id' => ServicePage::VALID_ID,
            'type' => ServiceEventPage::INVALID_TYPE
        ));

        $I->prepareRequest();
        $I->sendDELETE($url);
        $I->seeResponseCodeIs(HTTP_STATUS_NOT_FOUND);
    }

    /**
     * @group service
     * @group event
     * @group entity
     * @group delete
     * @group notFound
     */
    public function notDeleteEventOnInvalidService(ApiGuy $I)
    {
        $this->fetchTestData();

        $I->wantToTest('I can not delete an event on an invalid service');
        $url = ServiceEventPage::getUrl(ServicePage::INVALID_ID, ServiceEventPage::VALID_TYPE);

        $I->dontSeeInDatabase('service', array('id' => ServicePage::INVALID_ID));

        $I->prepareRequest();
        $I->sendDELETE($url);
        $I->seeResponseCodeIs(HTTP_STATUS_NOT_FOUND);
    }

    /**
     * @group service
     * @group event
     * @group entity
     * @group post
     * @group notAllowed
     */
    public function checkNotAllowedMethods(ApiGuy $I)
    {
        $this->fetchTestData();

        $I->wantToTest('that all not allowed methods return the correct status');
        $url = ServiceEventPage::getUrl(ServicePage::VALID_ID, ServiceEventPage::VALID_TYPE);

        foreach (ServiceEventPage::notAllowedMethods() as $method) {
            $method = 'send' . strtoupper($method);

            $I->prepareRequest();
            $I->{$method}($url);
            $I->seeResponseCodeIs(HTTP_STATUS_METHOD_NOT_ALLOWED);
        }
    }
}