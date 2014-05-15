<?php

use Phinx\Migration\AbstractMigration;

class AllowEmptyAddresses extends AbstractMigration
{
    protected $columns = [
        'addressCompanyName' => true,
        'addressFullName' => true,
        'address1' => true,
        'address2' => true,
        'address3' => true,
        'addressCity' => true,
        'addressCounty' => true,
        'addressCountry' => true,
        'addressPostcode' => true,
        'emailAddress' => true,
        'phoneNumber' => true,
        'addressCountryCode' => true,
    ];

    /**
     * Migrate Up.
     */
    public function up()
    {
        $address = $this->table('address');
        foreach ($address->getColumns() as $column) {
            if (!isset($this->columns[$column->getName()])) {
                continue;
            }

            $column->setNull(true);
            $address->changeColumn($column->getName(), $column);
        }
        $address->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $address = $this->table('address');
        foreach ($address->getColumns() as $column) {
            if (!isset($this->columns[$column->getName()])) {
                continue;
            }

            $column->setNull(false);
            $address->changeColumn(null, $column);
        }
        $address->save();
    }
}
