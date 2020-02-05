<?php
use Phinx\Migration\AbstractOnlineSchemaChange;
use Phinx\Migration\EnvironmentAwareInterface;

class Utf8FourByteColumns extends AbstractOnlineSchemaChange implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    protected function getColumnsToUpdate()
    {
        return [
            'alert' => ['alert' => 'TEXT'],
            'giftWrap' => ['giftWrapMessage' => 'LONGTEXT'],
            'note' => ['note' => 'TEXT'],
            'order' => ['buyerMessage' => ['TEXT', 'MEDIUMTEXT']],
            'orderLive' => ['buyerMessage' => ['TEXT', 'MEDIUMTEXT']],
        ];
    }

    /**
     * Migrate Up.
     */
    public function up()
    {
        foreach ($this->getColumnsToUpdate() as $table => $columns) {
            $alterTable = implode(
                ', ',
                array_map(
                    function($column, $type) {
                        if (!is_array($type)) {
                            $type = [$type, $type];
                        }
                        return sprintf('MODIFY COLUMN `%s` %s CHARACTER SET "utf8mb4" COLLATE "utf8mb4_unicode_ci" NOT NULL', $column, $type[1]);
                    },
                    array_keys($columns),
                    array_values($columns)
                )
            );
            $this->onlineSchemaChange($table, $alterTable);
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        foreach ($this->getColumnsToUpdate() as $table => $columns) {
            $alterTable = implode(
                ', ',
                array_map(
                    function($column, $type) {
                        if (!is_array($type)) {
                            $type = [$type, $type];
                        }
                        return sprintf('MODIFY COLUMN `%s` %s NOT NULL', $column, $type[0]);
                    },
                    array_keys($columns),
                    array_values($columns)
                )
            );
            $this->onlineSchemaChange($table, $alterTable);
        }
    }
}
