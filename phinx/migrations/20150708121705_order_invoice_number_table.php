<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class OrderInvoiceNumberTable extends AbstractMigration implements EnvironmentAwareInterface
{
    const TABLE = 'orderInvoice';

    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    /**
     * Migrate Up.
     */
    public function up()
    {
        $this
            ->table(static::TABLE, ['id' => false, 'primary_key' => ['orderId'], 'collation' => 'utf8_general_ci'])
            ->addColumn('orderId', 'string', ['limit' => 120])
            ->addColumn('rootOrganisationUnitId', 'integer')
            ->addColumn('invoiceNumber', 'integer')
            ->addIndex(['rootOrganisationUnitId', 'invoiceNumber'], ['unique' => true])
            ->create();

        foreach (['order', 'orderLive'] as $table) {
            $this->execute(
                sprintf(
                    'INSERT IGNORE INTO %s SELECT id as orderId, rootOrganisationUnitId, invoiceNumber FROM `%s` WHERE invoiceNumber IS NOT NULL',
                    static::TABLE,
                    $table
                )
            );

            $this->table($table)
                ->removeIndex(['rootOrganisationUnitId', 'invoiceNumber'])
                ->removeColumn('invoiceNumber')
                ->update();
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        foreach (['order', 'orderLive'] as $table) {
            $this
                ->table($table)
                ->addColumn('invoiceNumber', 'integer', ['null' => true])
                ->addIndex(['rootOrganisationUnitId', 'invoiceNumber'], ['unique' => true])
                ->update();

            $this->execute(
                sprintf(
                    'UPDATE `%s` o JOIN %s i ON o.id = i.orderId SET o.invoiceNumber = i.invoiceNumber',
                    $table,
                    static::TABLE
                )
            );
        }

        $this->table(static::TABLE)->drop();
    }
}
