<?php
namespace CG\Skeleton\Chef;

interface EnvironmentInterface
{
    public function getName();

    public function setupIp();
}