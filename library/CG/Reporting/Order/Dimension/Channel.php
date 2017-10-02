<?php
namespace CG\Reporting\Order\Dimension;

class Channel implements DimensionInterface
{
    const KEY = 'channel';

    public function getSelect()
    {
        return self::KEY;
    }

    public function getKey()
    {
        return self::KEY;
    }

    public function getGroupBy()
    {
        return self::KEY;
    }
}
