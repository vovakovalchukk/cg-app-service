<?php
namespace CG\Skeleton;

interface Command
{
    public function getName();
    public function run(Arguments $arguments, Config $config);
}