<?php
namespace CG\Stock\Audit\Adjustment\Storage;

use CG\Stdlib\CollectionInterface;
use CG\Stock\Audit\Adjustment\MigrationTimer;

class ArchiveDb extends Db
{
    const LOG_CODE = parent::LOG_CODE . '::Archive';

    public function saveCollection(CollectionInterface $collection, MigrationTimer $migrationTimer = null)
    {
        $timer = $migrationTimer->getUploadTimer();
        try {
            return parent::saveCollection($collection);
        } finally {
            $timer();
        }
    }
}