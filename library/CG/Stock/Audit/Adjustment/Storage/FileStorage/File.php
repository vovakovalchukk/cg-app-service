<?php
namespace CG\Stock\Audit\Adjustment\Storage\FileStorage;

use function CG\Stdlib\flatten;
use CG\Stock\Audit\Adjustment\Entity as AuditAdjustment;

class File extends \ArrayObject
{
    /** @var string */
    protected $filename;
    /** @var bool */
    protected $compressed;
    /** @var int */
    protected $initialCount;
    /** @var ?string */
    protected $hash;

    public function __construct(string $filename, bool $compressed)
    {
        parent::__construct();
        $this->filename = $filename;
        $this->compressed = $compressed;
        $this->initialCount = 0;
        $this->hash = null;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function isCompressed(): bool
    {
        return $this->compressed;
    }

    public function getInitialCount(): int
    {
        return $this->initialCount;
    }

    /**
     * @return self
     */
    public function setInitialCount(int $initialCount)
    {
        $this->initialCount = $initialCount;
        return $this;
    }

    public function hash(): string
    {
        return md5(implode('::', flatten($this->toArray())));
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @return self
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
        return $this;
    }

    public function isModified(): bool
    {
        return $this->hash !== $this->hash();
    }

    public function toArray(): array
    {
        $array = [];
        /** @var AuditAdjustment $entity */
        foreach ($this as $entity) {
            $array[] = $entity->toArray();
        }
        return $array;
    }
}