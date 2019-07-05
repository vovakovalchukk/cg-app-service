<?php

use Phinx\Migration\AbstractMigration;

class AmazonCategoryExternalData extends AbstractMigration
{
    public function change()
    {
        $this
            ->table('amazonCategoryExternalData', ['row_format' => 'COMPRESSED'])
            ->addColumn('data', 'json')
            ->create();
    }
}