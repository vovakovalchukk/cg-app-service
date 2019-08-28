<?php

use Phinx\Migration\AbstractMigration;

class AmazonCategoryExternalData extends AbstractMigration
{
    public function change()
    {
        $this
            ->table('amazonCategoryExternalData', ['row_format' => 'COMPRESSED', 'id' => false, 'primary_key' => 'id'])
            ->addColumn('id', 'integer', ['limit' => 10, 'signed' => false])
            ->addColumn('data', 'json')
            ->create();
    }
}