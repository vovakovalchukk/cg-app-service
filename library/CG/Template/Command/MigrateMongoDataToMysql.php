<?php

namespace CG\Template\Command;

use CG\ETag\Exception\Conflict as ConflictException;
use CG\Settings\InvoiceMapping\Filter as InvoiceMappingFilter;
use CG\Settings\InvoiceMapping\Repository as InvoiceMappingRepository;
use CG\Stdlib\Exception\Runtime\NotFound as NotFoundException;
use CG\Template\Repository as TemplateRepository;
use CG\Template\Storage\Db as MySQLStorage;
use CG\Template\Storage\MongoDb as MongoDb;

class MigrateMongoDataToMysql
{
    protected $db;
    /** @var TemplateRepository */
    protected $templateRepository;
    /** @var InvoiceMappingRepository */
    protected $invoiceMappingRepository;
    protected $mongoDb;

    public function __construct(
        MySQLStorage $db,
        MongoDb $mongoDb,
        TemplateRepository $templateRepository,
        InvoiceMappingRepository $invoiceMappingRepository
    )
    {
        $this->db = $db;
        $this->mongoDb = $mongoDb;
        $this->templateRepository = $templateRepository;
        $this->invoiceMappingRepository = $invoiceMappingRepository;
    }

    public function __invoke()
    {
        $migratedCount = $this->migrate();
        $reindexedCount = $this->reindexInvoiceMappings();
        return (object)[
            'migrated' => $migratedCount,
            'reindexed' => $reindexedCount,
        ];
    }

    protected function migrate()
    {
        $entityArray = [];
        $updated = 0;
        $page = 1;
        do {
            try {

                $collection = $this->mongoDb
                    ->fetchCollectionByPagination(50, $page, [], [], []);
                array_merge($entityArray, $collection->toArray());
                $page++;
            } catch (NotFoundException $unused) {
                break;
            }
        } while (true);

        foreach ($entityArray as $entity) {
            try {
                $this->db->save($entity);
                $updated++;
            } catch (NotFoundException|ConflictException $exception) {
                continue;
            }
        }

        return $updated;
    }

    protected function reindexInvoiceMappings()
    {

        $updated = 0;
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
                    try {
                        $invoice = $this->templateRepository->fetch($invoiceMapping->getInvoiceId());
                    } catch (NotFoundException $cantUpdate) {
                        continue;
                    }
                    $invoices[$invoiceMapping->getInvoiceId()] = $invoice;
                }
                $invoiceMapping->setInvoiceId($invoice->getId());

                try {
                    $this->invoiceMappingRepository->save($invoiceMapping);
                    $updated++;
                } catch (NotFoundException|ConflictException $exception) {
                    continue;
                }
            }
        } while (true);

        return $updated;
    }
}