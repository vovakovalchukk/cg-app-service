<?php
namespace CG\Stock\Audit\Adjustment;

class MigrationTimer
{
    protected const PRECISION = 4;

    /** @var float */
    protected $total = 0.0;
    /** @var float */
    protected $load = 0.0;
    /** @var float */
    protected $compression = 0.0;
    /** @var float */
    protected $upload = 0.0;

    public function getTotal(): float
    {
        return round($this->total, static::PRECISION);
    }

    public function getTotalTimer(): callable
    {
        return $this->getTimer('total');
    }

    public function getLoad(): float
    {
        return round($this->load, static::PRECISION);
    }

    public function getLoadTimer(): callable
    {
        return $this->getTimer('load');
    }

    public function getCompression(): float
    {
        return round($this->compression, static::PRECISION);
    }

    public function getCompressionTimer(): callable
    {
        return $this->getTimer('compression');
    }

    public function getUpload(): float
    {
        return round($this->upload, static::PRECISION);
    }

    public function getUploadTimer(): callable
    {
        return $this->getTimer('upload');
    }

    protected function getTimer(string $property): callable
    {
        $timer = microtime(true);
        return function() use($property, &$timer) {
            if ($timer === null) {
                return;
            }

            $this->{$property} += microtime(true) - $timer;
            $timer = null;
        };
    }
}