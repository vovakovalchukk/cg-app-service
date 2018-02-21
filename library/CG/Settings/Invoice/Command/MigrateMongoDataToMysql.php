<?php

namespace CG\Settings\Invoice\Command;

use CG\Settings\Invoice\Service\Storage\Db as InvoiceSettingsDbStorage;
use CG\Settings\Invoice\Shared\Repository as InvoiceSettingsRepository;
use CG\Stdlib\Exception\Runtime\NotFound as NotFoundException;
use CG\Settings\Invoice\Shared\Filter as InvoiceSettingsFilter;

class MigrateMongoDataToMysql
{
    protected $db;
    /** @var InvoiceSettingsRepository */
    protected $invoiceSettingsRepository;

    public function __construct(
        InvoiceSettingsDbStorage $db,
        InvoiceSettingsRepository $invoiceSettingsRepository
    ) {
        $this->db = $db;
        $this->invoiceSettingsRepository = $invoiceSettingsRepository;
    }

    public function __invoke()
    {
        $collection = $this->migrate();

        return count($collection);
    }

    protected function migrate()
    {
        $entityArray = [];
        $page = 1;
        $filter = new InvoiceSettingsFilter(50, $page);
        do {
            $filter->setPage($page);
            $page++;
            try {
                $collection = $this->invoiceSettingsRepository
                    ->fetchCollectionByFilter($filter);
                array_merge($entityArray, $collection->toArray());
            } catch (NotFoundException $unused) {
                break;
            }

            foreach($collection as $invoiceSetting) {
                $this->db->save($invoiceSetting);
            }
        } while (true);

        return $entityArray;
    }
}