<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class OrderRootOu extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    /**
     * Migrate Up.
     */
    public function up()
    {
        if (!$this->hasTable('account.account')) {
            return;
        }
        $sql = 'SELECT o.id, ou.id as rootOrganisationUnitId'
            . ' FROM `%1$s` o'
            . ' JOIN ('
                . ' SELECT o.id, MIN(root.left) as `left`, MAX(root.right) as `right`'
                . ' FROM `%1$s` o'
                . ' JOIN account.account a ON o.accountId = a.id'
                . ' JOIN directory.organisationUnit ou ON a.organisationUnitId = ou.id'
                . ' JOIN directory.organisationUnit root ON ou.left >= root.left AND ou.right <= root.right'
                . ' WHERE o.rootOrganisationUnitId IS NULL'
                . ' GROUP BY o.id'
            . ' ) tbl ON o.id = tbl.id'
            . ' JOIN directory.organisationUnit ou ON tbl.left = ou.left and tbl.right = ou.right';

        $update = 'UPDATE `%1$s` SET rootOrganisationUnitId = %2$d WHERE id = "%3$s"';

        foreach (['order', 'orderLive'] as $table) {
            $rows = $this->fetchAll(sprintf($sql, $table));
            foreach ($rows as $row) {
                $this->execute(sprintf($update, $table, $row['rootOrganisationUnitId'], $row['id']));
            }
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        // Change can not be undone
    }
}
