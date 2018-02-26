<?php

namespace CG\Settings\Invoice\Command;

use CG\ETag\Exception\Conflict as ConflictException;
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
        $count = $this->migrate();

        return $count;
    }

    protected function migrate()
    {
        $updated = 0;
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
                try {
                    $this->db->save($invoiceSetting);
                    $updated++;
                } catch (NotFoundException|ConflictException $exception) {
                    continue;
                }
            }
        } while (true);

        return $updated;
    }
}