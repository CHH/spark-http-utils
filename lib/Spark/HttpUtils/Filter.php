<?php

namespace Spark\HttpUtils;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Abstract middleware class
 */
abstract class Filter extends Middleware
{
    function before(Request $request)
    {}

    function after(Request $request, Response $response)
    {}

    function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        if ($beforeResponse = $this->before($request) and $beforeResponse instanceof Response) {
            return $beforeResponse;
        }

        $response = $this->app->handle($request, $type, $catch);

        if ($afterResponse = $this->after($request, $response) and $afterResponse instanceof Response) {
            return $afterResponse;
        }

        return $response;
    }
}

