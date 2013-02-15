<?php

namespace Spark\HttpUtils\Middleware;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Abstract middleware class
 */
abstract class Middleware
    implements HttpKernelInterface
{
    protected $app;

    function __construct(HttpKernelInterface $app)
    {
        $this->app = $app;
    }
}

