<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class AmazonCategoryExternalData extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $this
            ->table('amazonCategoryExternalData', ['row_format' => 'COMPRESSED', 'id' => false, 'primary_key' => 'id'])
            ->addColumn('id', 'integer', ['limit' => 10, 'signed' => false])
            ->addColumn('data', 'json')
            ->create();
    }
}