<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class ListingReplacedBy extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'listings';
    }

    public function change()
    {
        $this->table('listing')
            ->addColumn('replacedById', 'integer', ['signed' => false, 'null' => true])
            ->update();
    }
}