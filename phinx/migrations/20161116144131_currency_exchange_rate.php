<?php
use Phinx\Migration\AbstractMigration;

class CurrencyExchangeRate extends AbstractMigration
{
    public function change()
    {
        $this->table('exchangeRate', ['id' => false, 'primary_key' => ['date', 'currencyCode', 'baseCurrencyCode'], 'collation' => 'utf8_general_ci'])
            ->addColumn('date', 'date')
            ->addColumn('currencyCode', 'string')
            ->addColumn('baseCurrencyCode', 'string')
            ->addColumn('rate', 'decimal', ['precision' => 12, 'scale' => 4, 'null' => true])
            ->create();
    }
}
