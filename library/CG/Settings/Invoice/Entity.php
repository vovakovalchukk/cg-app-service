<?php
namespace CG\Settings\Invoice;

use CG\Template\PaperPage;
use CG\Template\TagReplace\TagReplaceableInterface;
use CG\Stdlib\CachableEntityTrait;
use CG\Stdlib\CachableInterface;
use CG\ETag\ETagInterface;
use CG\ETag\EntityTrait as ETagEntityTrait;
use CG\ETag\StoredETagInterface;
use CG\ETag\StoredETagTrait as StoredETagEntityTrait;
use CG\Permission\OwnershipInterface;
use CG\Permission\OwnershipTrait;
use \JsonSerializable;
use Zend\EventManager\GlobalEventManager;
use CG\Permission\Ownership;

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
        return $this;
    }

    public function getOrganisationUnitId()
    {
        return $this->id;
    }

    public function setOrganisationUnitId($organisationUnitId)
    {
        $this->id = $organisationUnitId;
        GlobalEventManager::trigger(Ownership::EVENT_OU_CHANGED_AFTER, Ownership::CONTEXT, array('entity' => $this));
        return $this;
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