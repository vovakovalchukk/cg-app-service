<?php
use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

/**
 * @property MysqlAdapter $adapter
 */
class OrderItemCgCreationDate extends AbstractMigration
{
    const TABLE = 'item';

    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->onlineSchemaChange(static::TABLE, 'ADD COLUMN cgCreationDate DATETIME');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->onlineSchemaChange(static::TABLE, 'REMOVE COLUMN cgCreationDate');
    }

    protected function onlineSchemaChange($table, $alter)
    {
        $options = $this->adapter->getOptions();

        $dsn = [
            'h=' . addcslashes($options['host'], ','),
            'u=' . addcslashes($options['user'], ','),
            'p=' . addcslashes($options['pass'], ','),
            'D=' . addcslashes($options['name'], ','),
            't=' . addcslashes($table, ','),
        ];

        if (isset($options['port'])) {
            $dsn[] = 'P=' . addcslashes($options['port'], ',');
        }
        if (isset($options['unix_socket'])) {
            $dns[] = 'S=' . addcslashes($options['unix_socket'], ',');
        }
        if (isset($options['charset'])) {
            $dns[] = 'A=' . addcslashes($options['charset'], ',');
        }

        $command = 'pt-online-schema-change';
        $arguments = [
            '--execute',
            '--alter ' . escapeshellarg($alter),
            escapeshellarg(implode(',', $dsn)),
        ];

        passthru($command . ' ' . implode(' ', $arguments), $return);
        if ($return !== 0) {
            throw new RuntimeException('Failed to update ' . $table);
        }
    }
}
