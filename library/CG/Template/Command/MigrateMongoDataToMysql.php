<?php

namespace CG\Template\Command;

use CG\Settings\InvoiceMapping\Repository as InvoiceMappingRepository;
use CG\Settings\InvoiceMapping\Filter as InvoiceMappingFilter;
use CG\Template\Storage\Db as MySQLStorage;
use CG\Template\Repository as TemplateRepository;
use CG\Stdlib\Exception\Runtime\NotFound as NotFoundException;

class MigrateMongoDataToMysql
{
    protected $db;
    /** @var TemplateRepository */
    protected $templateRepository;
    /** @var InvoiceMappingRepository */
    protected $invoiceMappingRepository;

    public function __construct(
        MySQLStorage $db,
        TemplateRepository $templateRepository,
        InvoiceMappingRepository $invoiceMappingRepository
    )
    {
        $this->db = $db;
        $this->templateRepository = $templateRepository;
        $this->invoiceMappingRepository = $invoiceMappingRepository;
    }

    public function __invoke()
    {
        $collection = $this->migrate();
        $this->reindexInvoiceMappings();
        return count($collection);
    }

    protected function migrate()
    {
        $entityArray = [];
        $page = 1;
        do {
            try {

                $collection = $this->templateRepository
                    ->fetchCollectionByPagination(50, $page, [], [], []);
                array_merge($entityArray, $collection->toArray());
                $page++;
            } catch (NotFoundException $unused) {
                break;
            }
        } while (true);

        foreach ($entityArray as $entity) {
            $this->db->save($entity);
        }

        return $entityArray;
    }

    protected function reindexInvoiceMappings()
    {
        $invoices = [];
        $invoiceMappingFilter = new InvoiceMappingFilter();
        $invoiceMappingFilter->setLimit(50);
        $page = 1;
        do {
            $invoiceMappingFilter->setPage($page);
            $page++;
            try {
                $invoiceMappings = $this->invoiceMappingRepository->fetchCollectionByFilter($invoiceMappingFilter);
            } catch (NotFoundException $notFound) {
                break;
            }
            /** @var \CG\Settings\InvoiceMapping\Entity $invoiceMapping */
            foreach ($invoiceMappings as $invoiceMapping) {
                if (isset($invoices[$invoiceMapping->getInvoiceId()])) {
                    $invoice = $invoices[$invoiceMapping->getInvoiceId()];
                } else {
                    $invoice = $this->templateRepository->fetch($invoiceMapping->getInvoiceId());
                    $invoices[$invoiceMapping->getInvoiceId()] = $invoice;
                }
                $invoiceMapping->setInvoiceId($invoice->getId());
                $this->invoiceMappingRepository->save($invoiceMapping);
            }
        } while (true);
    }
}