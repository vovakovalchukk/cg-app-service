<?php
use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

/**
 * @property MysqlAdapter $adapter
 */
class OrderVatNumber extends AbstractMigration implements EnvironmentAwareInterface
{
    const TABLE = 'orderInvoice';

    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function up()
    {
        $this->onlineSchemaChange(static::TABLE, 'ADD COLUMN `vatNumber` varchar(255) NULL, CHANGE COLUMN `invoiceNumber` `invoiceNumber` int(11) NULL');
    }

    public function down()
    {
        $this->onlineSchemaChange(static::TABLE, 'DROP COLUMN `vatNumber`, CHANGE COLUMN `invoiceNumber` `invoiceNumber` int(11) NOT NULL');
    }

    protected function onlineSchemaChange($table, $alter)
    {
        $options = $this->adapter->getOptions();
        $config = require dirname(dirname(__DIR__)) . '/config/storage.local.php';
        if (!isset($config[$options['name']])) {
            throw new InvalidArgumentException('Root access is not configured for database ' . $options['name']);
        }

        $dsn = [
            'h=' . addcslashes($config[$options['name']]['hostname'], ','),
            'u=' . addcslashes($config[$options['name']]['username'], ','),
            'p=' . addcslashes($config[$options['name']]['password'], ','),
            'D=' . addcslashes($config[$options['name']]['database'], ','),
            't=' . addcslashes($table, ','),
        ];

        if (isset($config[$options['name']]['port'])) {
            $dsn[] = 'P=' . addcslashes($config[$options['name']]['port'], ',');
        }
        if (isset($config[$options['name']]['unix_socket'])) {
            $dns[] = 'S=' . addcslashes($config[$options['name']]['unix_socket'], ',');
        }
        if (isset($config[$options['name']]['charset'])) {
            $dns[] = 'A=' . addcslashes($config[$options['name']]['charset'], ',');
        }

        $command = 'pt-online-schema-change';
        $arguments = [
            '--execute',
            '--alter ' . escapeshellarg($alter),
            '--alter-foreign-keys-method ' . escapeshellarg('auto'),
            escapeshellarg(implode(',', $dsn)),
        ];

        passthru($command . ' ' . implode(' ', $arguments), $return);
        if ($return !== 0) {
            throw new RuntimeException('Failed to update ' . $table);
        }
    }
}
