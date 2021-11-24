<?php

use Phinx\Migration\AbstractOnlineSchemaChange;

class AddressUtf8Columns extends AbstractOnlineSchemaChange
{
    protected const TABLE = 'address';
    protected const COLUMNS = [
        [
            'addressCompanyName',
            'varchar(255)',
            'utf8mb4_0900_ai_ci',
        ],
        [
            'addressFullName',
            'varchar(255)',
            'utf8mb4_0900_ai_ci',
        ],
        [
            'address1',
            'varchar(255)',
            'utf8mb4_0900_ai_ci',
        ],
        [
            'address2',
            'varchar(255)',
            'utf8mb4_0900_ai_ci',
        ],
        [
            'address3',
            'varchar(255)',
            'utf8mb4_0900_ai_ci',
        ],
        [
            'addressCity',
            'varchar(255)',
            'utf8mb4_0900_ai_ci',
        ],
        [
            'addressCounty',
            'varchar(255)',
            'utf8mb4_0900_ai_ci',
        ],
        [
            'addressCountry',
            'varchar(255)',
            'utf8mb4_0900_ai_ci',
        ],
        [
            'addressPostcode',
            'varchar(20)',
            'utf8mb4_0900_ai_ci',
        ],
        [
            'emailAddress',
            'varchar(255)',
            'utf8mb4_0900_ai_ci',
        ],
        [
            'phoneNumber',
            'varchar(255)',
            'utf8mb4_0900_ai_ci',
        ],
    ];

    public function up()
    {
        $this->onlineSchemaChange(
            static::TABLE,
            $this->buildModifyColumnCollateStatement(static::COLUMNS)
        );
    }

    public function down()
    {
        // we don't want to reverse this
        return;
    }
}
