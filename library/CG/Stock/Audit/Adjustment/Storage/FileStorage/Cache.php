<?php
namespace CG\Stock\Audit\Adjustment\Storage\FileStorage;

use Predis\Client as Predis;

class Cache
{
    protected const KEY = 'AuditAdjustment::FileCache';

    /** @var Predis */
    protected $predis;

    public function __construct(Predis $predis)
    {
        $this->predis = $predis;
    }

    public function markFileAsLoaded(string $filename): void
    {
        $this->predis->sadd(static::KEY, $filename);
    }

    public function markFileAsDirty(string $filename): void
    {
        $this->predis->srem(static::KEY, $filename);
    }

    public function isFileLoader(string $filename): bool
    {
        return (bool) $this->predis->sismember(static::KEY, $filename);
    }
}