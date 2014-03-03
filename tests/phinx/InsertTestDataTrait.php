<?php

trait InsertTestDataTrait
{
    protected function insertTestData($table, array $data)
    {
        $rows = array();
        foreach ($data as $row) {
            $rows[] = implode('","',$row);
        }
        $rows = implode('"),("',$rows);
        $this->execute('INSERT INTO `'.$table.'` VALUES ("'.$rows.'")');
    }

    /**
     * Implemented by Phinx\Migration\AbstractMigration
     */
    abstract public function execute($sql);
}