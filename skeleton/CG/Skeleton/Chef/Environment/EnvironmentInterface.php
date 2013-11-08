<?php
namespace CG\Skeleton\Chef\Environment;

interface EnvironmentInterface
{
    public function getName();

    public function setupIp();
}