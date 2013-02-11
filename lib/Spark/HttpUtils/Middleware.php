<?php

namespace Spark\HttpUtils;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Abstract middleware class
 */
abstract class Middleware
    implements HttpKernelInterface, TerminableInterface
{
    protected $app;

    function __construct(HttpKernelInterface $app)
    {
        $this->app = $app;
    }

    function terminate(Request $request, Response $response)
    {
        if ($this->app instanceof TerminableInterface) {
            $this->app->terminate($request, $response);
        }
    }
}

