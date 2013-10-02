<?php
namespace CG\Skeleton;

interface ShutdownCommand
{
    public function run(Arguments $arguments, Config $config);
}