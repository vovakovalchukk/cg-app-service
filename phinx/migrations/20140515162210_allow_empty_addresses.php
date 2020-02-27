<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class AllowEmptyAddresses extends AbstractMigration implements EnvironmentAwareInterface
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

    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

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
