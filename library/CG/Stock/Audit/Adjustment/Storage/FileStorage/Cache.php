<?php
namespace CG\Stock\Audit\Adjustment\Storage\FileStorage;

use CG\Stdlib\Exception\Runtime\NotFound;
use Predis\Client as Predis;

class Cache
{
    protected const KEY_PREFIX = 'AuditAdjustment:FileCache:';
    protected const EXPIRE_AFTER_SECONDS = 60 * 60 * 24 * 365; // 1 year

    /** @var Predis */
    protected $predis;

    public function __construct(Predis $predis)
    {
        $this->predis = $predis;
    }

    protected function generateCacheKey(string $filename): string
    {
        return static::KEY_PREFIX . $filename;
    }

    public function loadFile(string $filename): string
    {
        $data = $this->predis->get($this->generateCacheKey($filename));
        if ($data !== null) {
            return $data;
        }
        throw new NotFound(sprintf('Could not find %s in cache', $filename));
    }

    public function saveFile(string $filename, string $data): void
    {
        $this->predis->setex($this->generateCacheKey($filename), static::EXPIRE_AFTER_SECONDS, $data);
    }

    public function removeFile(string $filename): void
    {
        $this->predis->del($this->generateCacheKey($filename));
    }
}