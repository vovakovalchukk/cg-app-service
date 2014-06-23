<?php
namespace CG\Settings\Invoice;

use \JsonSerializable;
use CG\ETag\ETagInterface;
use CG\ETag\EntityTrait as ETagEntityTrait;
use CG\ETag\StoredETagInterface;
use CG\ETag\StoredETagTrait as StoredETagEntityTrait;
use CG\Permission\OwnershipInterface;
use CG\Permission\Ownership;
use CG\Stdlib\CachableEntityTrait;
use CG\Stdlib\CachableInterface;
use Zend\EventManager\GlobalEventManager;

class Entity implements CachableInterface, ETagInterface, StoredETagInterface, OwnershipInterface, JsonSerializable
{
    use CachableEntityTrait, ETagEntityTrait, StoredETagEntityTrait;

    protected $id;
    protected $default;
    protected $tradingCompanies;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        GlobalEventManager::trigger(Ownership::EVENT_OU_CHANGED_AFTER, Ownership::CONTEXT, ['entity' => $this]);
        return $this;
    }

    public function getOrganisationUnitId()
    {
        return $this->getId();
    }

    public function setOrganisationUnitId($organisationUnitId)
    {
        return $this->setId($organisationUnitId);
    }

    public function getDefault()
    {
        return $this->default;
    }

    public function setDefault($default)
    {
        $this->default = $default;
        return $this;
    }
    public function getTradingCompanies()
    {
        return $this->tradingCompanies;
    }

    public function setTradingCompanies($tradingCompanies)
    {
        $this->tradingCompanies = $tradingCompanies;
        return $this;
    }

    public function toArray()
    {
        return [
            "id" => $this->getId(),
            "default" => $this->getDefault(),
            "tradingCompanies" => $this->getTradingCompanies()
        ];
    }

    public function jsonSerialize()
    {
        $data = $this->toArray();
        $data['storedETag'] = $this->getStoredETag();
        return $data;
    }

    public function getEtagDataArray()
    {
        return $this->toArray();
    }
}