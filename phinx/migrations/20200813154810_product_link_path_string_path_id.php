<?php

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class ProductLinkPathStringPathId extends AbstractMigration implements EnvironmentAwareInterface
{
    protected const TABLE_NAME = 'productLinkPath';

    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function up()
    {
        $this
            ->table(static::TABLE_NAME)
            ->changeColumn('pathId', 'string', ['null' => false, 'signed' => false])
            ->update();
    }

    public function down()
    {
        $highestNumericPathId = $this->getHighestNumericPathId();
        $stringPathIds = $this->query('SELECT DISTINCT pathId FROM productLinkPath WHERE pathId NOT REGEXP \'^[0-9]+$\'');
        foreach ($stringPathIds as $stringPathId) {
            $this->updatePathId($stringPathId['pathId'], ++$highestNumericPathId);
        }
        $this
            ->table(static::TABLE_NAME)
            ->changeColumn('pathId', 'integer', ['null' => false, 'signed' => false])
            ->update();
    }

    protected function getHighestNumericPathId(): int
    {
        $results = $this->query('SELECT MAX(pathId) as highestNumericPathId FROM productLinkPath WHERE pathId REGEXP \'^[0-9]+$\'');
        $result = current(iterator_to_array($results));
        return (int)$result['highestNumericPathId'];
    }

    protected function updatePathId(string $oldPathId, int $newPathId): void
    {
        /** @var MysqlAdapter $adapter */
        $adapter = $this->getAdapter();
        $update = $adapter->getConnection()->prepare('UPDATE productLinkPath SET pathId = ? WHERE pathId = ?');

        try {
            $update->execute([$newPathId, $oldPathId]);
        } finally {
            $update->closeCursor();
        }
    }
}
