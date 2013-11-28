<?php
namespace CG\Slim\Versioning;

use Slim\Middleware as SlimMiddleware;

class Middleware extends SlimMiddleware
{
    public function call()
    {
        $this->next->call();
    }
}