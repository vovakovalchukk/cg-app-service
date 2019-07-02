<?php
namespace CG\Stock\Audit\Adjustment\Storage;

use CG\Stdlib\CollectionInterface;
use CG\Stock\Audit\Adjustment\MigrationTimer;

class ArchiveDb extends Db
{
    const TABLE = parent::TABLE . 'Archive';
    const LOG_CODE = parent::LOG_CODE . '::Archive';

    public function saveCollection(CollectionInterface $collection, MigrationTimer $migrationTimer = null)
    {
        $timer = $migrationTimer ? $migrationTimer->getUploadTimer() : function() {};
        try {
            return parent::saveCollection($collection);
        } finally {
            $timer();
        }
    }
}