<?php

namespace Spark\HttpUtils;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class StackedHttpKernel implements HttpKernelInterface, TerminableInterface
{
    protected $app;
    protected $middlewares;

    function __construct(HttpKernelInterface $app, array $middlewares = array())
    {
        $this->app = $app;
        $this->middlewares = $middlewares;
    }

    function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        return $this->app->handle($request, $type, $catch);
    }

    function terminate(Request $request, Response $response)
    {
        foreach ($this->middlewares as $middleware) {
            if ($middleware instanceof TerminableInterface) {
                $middleware->terminate($request, $response);
            }
        }
    }
}

