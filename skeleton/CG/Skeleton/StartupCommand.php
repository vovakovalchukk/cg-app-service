<?php
namespace CG\Skeleton;

interface StartupCommand
{
    public function run(Arguments $arguments, Config $config);
}