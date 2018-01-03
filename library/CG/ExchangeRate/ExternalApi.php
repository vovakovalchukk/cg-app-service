<?php
namespace CG\ExchangeRate\Storage;

use CG\ExchangeRate\Collection;
use CG\ExchangeRate\Entity;
use CG\ExchangeRate\Filter;
use CG\ExchangeRate\Mapper;
use CG\ExchangeRate\StorageInterface;
use CG\Http\Client\CollectionTrait;
use CG\Stdlib\Date;
use CG\Stdlib\Exception\Runtime\NotFound;
use GuzzleHttp\Client;

class ExternalApi implements StorageInterface
{
    const APP_ID = '84d05173ded84e87a3c5080a36bc8491';
    const API_URL = 'https://openexchangerates.org/api/historical/';
    const API_DATE_FORMAT = 'Y-m-d';

    use CollectionTrait;

    protected $mapper;
    protected $client;

    public function __construct(Client $client, Mapper $mapper)
    {
        $this->setClient($client)
            ->setMapper($mapper);
    }

    public function fetch($id)
    {
        $date = Entity::getComponentsFromId($id)['date'];
        $entity = $this->fetchCurrencyExchangeRates(new Date($date))->getById($id);
        if ($entity) {
            return $entity;
        }
        throw new NotFound(sprintf('Can\'t fetch exchange rate %s from external api', $id));
    }

    public function save($entity)
    {
        // Can't save to external api
        return $entity;
    }

    public function remove($entity)
    {
        // Can't remove from external api
    }

    public function fetchCollectionByFilter(Filter $filter)
    {
        $collection = $this->fetchCurrencyExchangeRates(new Date($filter->getDateFrom()));
        return $collection;
    }

    public function fetchCurrencyExchangeRates(Date $date)
    {
        $guzzleRequest = $this->client->createRequest('GET', $this->getAPIUrl($date));
        $guzzleResponse = $this->client->send($guzzleRequest);

        return $this->convertOpenExchangeRatesDataToEntities($guzzleResponse->json());
    }

    protected function convertOpenExchangeRatesDataToEntities($openExchangeRatesData)
    {
        $baseCurrencyCode = $openExchangeRatesData['base'];
        $timestamp = $openExchangeRatesData['timestamp'];
        $rates = $openExchangeRatesData['rates'];

        $collection = new Collection(Entity::class, __FUNCTION__);

        foreach ($rates as $currencyCode => $rate) {
            $date = date(static::API_DATE_FORMAT,$timestamp);
            $entity = $this->mapper->fromArray([
                'date' => $date,
                'currencyCode' => $currencyCode,
                'baseCurrencyCode' => $baseCurrencyCode,
                'rate' => $rate,
            ]);
            $collection->attach($entity);
        }

        return $collection;
    }

    protected function getAPIUrl(Date $date)
    {
        $parsedDateFilename = $date->getDate(Date::FORMAT).'.json';
        $queryStringKey = '?app_id=';
        return self::API_URL . $parsedDateFilename . $queryStringKey . self::APP_ID;
    }

    protected function getUrl()
    {
        return '/' . Mapper::ENTRY_POINT_NAME;
    }

    protected function setMapper(Mapper $mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }

    protected function getMapper()
    {
        return $this->mapper;
    }

    protected function setClient(Client $client)
    {
        $this->client = $client;
        return $this;
    }

    protected function getClient()
    {
        return $this->client;
    }
}