<?php
namespace CG\Stock\Audit\Adjustment\Storage\FileStorage;

class Worker
{
    protected const MAX_STACK_SIZE = 10;

    private function __construct() {}

    public static function generateWorker(iterable $work): \Generator
    {
        $stack = [];
        foreach ($work as $job) {
            array_push($stack, $job);
            while (count($stack) >= static::MAX_STACK_SIZE) {
                $newJob = yield array_pop($stack);
                if ($newJob !== null) {
                    array_push($stack, $newJob);
                }
            }
        }

        while ($job = array_pop($stack)) {
            $newJob = yield $job;
            if ($newJob !== null) {
                array_push($stack, $newJob);
            }
        }
    }
}