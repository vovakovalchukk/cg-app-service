<?php
namespace CG\Product\ChannelDetail\Storage\External;

use CG\Di\Di;
use function CG\Stdlib\hyphenToFullyQualifiedClassname;

class Factory
{
    /** @var Di */
    protected $di;

    public function __construct(Di $di)
    {
        $this->di = $di;
    }

    public function getStorageForChannel(string $channel): StorageInterface
    {
        $storageClass = hyphenToFullyQualifiedClassname($channel, 'CG') . '\\Product\\ChannelDetail\\External\\StorageInterface';
        if (!is_a($storageClass, StorageInterface::class, true)) {
            return new NullStorage();
        }
        return $this->di->newInstanceUsingTypePreferences($storageClass);
    }
}