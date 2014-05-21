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
        $this->setNull(true);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->setNull(false);
    }

    protected function setNull($null)
    {
        $address = $this->table('address');
        foreach ($address->getColumns() as $column) {
            if (!isset($this->columns[$column->getName()])) {
                continue;
            }

            $column->setNull($null);
            $address->changeColumn($column->getName(), $column);
        }
        $address->save();
    }
}
