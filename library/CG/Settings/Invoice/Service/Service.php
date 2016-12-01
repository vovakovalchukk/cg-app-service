<?php
namespace CG\Settings\Invoice\Service;

use CG\Settings\Invoice\Shared\Entity;
use CG\Settings\Invoice\Shared\Filter;
use CG\Settings\Invoice\Shared\Mapper;
use CG\Settings\Invoice\Shared\StorageInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Stdlib\ServiceTrait;

class Service implements LoggerAwareInterface
{
    use LogTrait;
    use ServiceTrait {
        fetch as traitFetch;
        save as traitSave;
    }

    const DEFAULT_LIMIT = 10;
    const DEFAULT_PAGE = 1;

    const SAVE_METHOD_CALLED = 'Invoice settings save method called. Id #%s';
    const ENTITY_EXISTS = 'Entity already exists. Updating id #%s';
    const ENTITY_NOT_EXISTS = 'Entity doesn\'t exist. Inserting id #%s';

    public function __construct(StorageInterface $repository, Mapper $mapper)
    {
        $this->setRepository($repository)
             ->setMapper($mapper);
    }

    public function fetchTemplateIdFromOrganisationUnitId($id, $tradingCompanyId = null)
    {
        $invoiceSettings = $this->fetch($id);
        $tradingCompanies = $invoiceSettings->getTradingCompanies();

        if (isset($tradingCompanies[$tradingCompanyId]['assignedInvoice'])) {
            return $tradingCompanies[$tradingCompanyId]['assignedInvoice'];
        }
        return $invoiceSettings->getDefault();

    }

    public function fetch($id)
    {
        try {
            return $this->traitFetch($id);
        } catch (NotFound $e) {
            return $this->getMapper()->fromArray([
                'id' => $id
            ]);
        }
    }

    public function fetchCollectionByPagination($limit, $page)
    {
        return $this->fetchCollectionByFilter(
            new Filter($limit, $page)
        );
    }

    public function fetchCollectionByFilter(Filter $filter)
    {
        $limit = $filter->getLimit() ?: static::DEFAULT_LIMIT;
        $page = $filter->getPage() ?: static::DEFAULT_PAGE;

        $collection = $this->getRepository()->fetchCollectionByFilter($filter);

        return $this->getMapper()->collectionToHal(
            $collection,
            "/settings/invoice",
            $limit,
            $page,
            $filter->toArray()
        );
    }

    public function remove(Entity $entity)
    {
        $this->getRepository()->remove($entity);
        return $this;
    }

    public function setRepository(StorageInterface $repository)
    {
        $this->repository = $repository;
        return $this;
    }

    public function getRepository()
    {
        return $this->repository;
    }

    public function setMapper(Mapper $mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }

    public function getMapper()
    {
        return $this->mapper;
    }
}
