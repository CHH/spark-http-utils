<?php

namespace Spark\HttpUtils;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Utility class for creating "filter" middlewares, i.e. middleware 
 * components which do something before or after the request was 
 * handled with the ability to easily short circuit or replace the 
 * response.
 *
 * @author Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 */
abstract class Filter extends Middleware
{
    /**
     * Gets called before the `handle` method is called.
     *
     * Returning a Response instance triggers a short circuit effect, and the `handle`
     * method is skipped.
     *
     * @api
     * @return null|Response
     */
    function before(Request $request)
    {}

    /**
     * Gets called after the `handle` method is called.
     *
     * Returning a Response instance from this filter causes the 
     * response returned from `handle` to be discarded and the returned 
     * response to be returned instead.
     *
     * @api
     * @return null|Response
     */
    function after(Request $request, Response $response)
    {}

    /**
     * {@inheritdoc}
     */
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

