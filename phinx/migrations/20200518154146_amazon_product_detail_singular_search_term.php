<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class AmazonProductDetailSingularSearchTerm extends AbstractMigration implements EnvironmentAwareInterface
{
    const TABLE = 'productAmazonDetail';

    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function up()
    {
        $this->addNewSearchTermsColumn();
        $this->copyDataFromOldColumnsToNewColumn();
        $this->dropOldSearchTermsColumns();
    }

    public function down()
    {
        $this->restoreOldSearchTermsColumns();
        $this->copyDataFromNewColumnToOldColumns();
        $this->dropNewSearchTermsColumn();
    }

    protected function addNewSearchTermsColumn(): void
    {
        $this
            ->table(static::TABLE)
            ->addColumn('searchTerms', 'string', ['limit' => 500, 'null' => true])
            ->update();
    }

    protected function dropOldSearchTermsColumns(): void
    {
        $this
            ->table(static::TABLE)
            ->removeColumn('searchTerm1')
            ->removeColumn('searchTerm2')
            ->removeColumn('searchTerm3')
            ->removeColumn('searchTerm4')
            ->removeColumn('searchTerm5')
            ->update();
    }

    protected function copyDataFromOldColumnsToNewColumn(): void
    {
        $query = <<<SQL
UPDATE `cg_app`.`productAmazonDetail`
SET `searchTerms` = LEFT(CONCAT_WS(", ", `searchTerm1`, `searchTerm2`, `searchTerm3`, `searchTerm4`, `searchTerm5`), 500)
SQL;
        $this->query($query);
    }

    protected function restoreOldSearchTermsColumns(): void
    {
        $this
            ->table(static::TABLE)
            ->addColumn('searchTerm1', 'string', ['limit' => 250, 'null' => true])
            ->addColumn('searchTerm2', 'string', ['limit' => 250, 'null' => true])
            ->addColumn('searchTerm3', 'string', ['limit' => 250, 'null' => true])
            ->addColumn('searchTerm4', 'string', ['limit' => 250, 'null' => true])
            ->addColumn('searchTerm5', 'string', ['limit' => 250, 'null' => true])
            ->update();
    }

    protected function dropNewSearchTermsColumn(): void
    {
        $this
            ->table(static::TABLE)
            ->removeColumn('searchTerms')
            ->update();
    }

    protected function copyDataFromNewColumnToOldColumns(): void
    {
        $query = <<<SQL
UPDATE `cg_app`.`productAmazonDetail`
SET `searchTerm1` = LEFT(`searchTerms`, 250),
`searchTerm2` = RIGHT(`searchTerms`, 250)
SQL;
        $this->query($query);
    }
}
