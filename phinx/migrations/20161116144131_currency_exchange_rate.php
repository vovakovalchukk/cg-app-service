<?php
use Phinx\Migration\AbstractMigration;

class CurrencyExchangeRate extends AbstractMigration
{
    public function change()
    {
        $this->table('exchangeRate', ['collation' => 'utf8_general_ci'])
            ->addColumn('datetime', 'datetime')
            ->addColumn('currencyCode', 'string')
            ->addColumn('baseCurrencyCode', 'string')
            ->addColumn('rate', 'decimal', ['precision' => 12, 'scale' => 4, 'null' => true])
            ->create();
    }
}
