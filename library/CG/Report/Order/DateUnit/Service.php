<?php
namespace CG\Report\Order\DateUnit;

class Service
{
    public function getSelect()
    {
        return 'DATE_FORMAT(purchaseDate, \'%Y-%m-%d\') as dateUnit';
    }

    public function getGroupBy()
    {
        return 'DATE_FORMAT(purchaseDate, \'%Y-%m-%d\')';
    }
}
