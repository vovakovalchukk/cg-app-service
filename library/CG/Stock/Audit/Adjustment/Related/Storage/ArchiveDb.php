<?php
namespace CG\Stock\Audit\Adjustment\Related\Storage;

class ArchiveDb extends Db
{
    protected const TABLE = parent::TABLE . 'Archive';
}