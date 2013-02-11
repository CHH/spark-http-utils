<?php

namespace Spark\HttpUtils;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Converts a plain callable into a HttpKernel.
 *
 * @author Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 */
class CallableKernel implements HttpKernelInterface, TerminableInterface
{
    /**
     * Callback which gets called on handle
     * @var callable
     */
    protected $handle;

    /**
     * Callback which gets called on terminate
     * @var callable
     */
    protected $terminate;

    function __construct($callback, $terminate = null)
    {
        $this->handle = $callback;
        $this->terminate = $terminate;
    }

    /**
     * Forwards arguments to the handle callback
     *
     * @param Request $request
     * @param $type
     * @param $catch
     *
     * @return Response
     */
    function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        return call_user_func($this->handle, $request, $type, $catch);
    }

    /**
     * Calls the terminate callback if available
     *
     * @param Request $request
     * @param Response $response
     * @return void
     */
    function terminate(Request $request, Response $response)
    {
        if (null !== $this->terminate) {
            return call_user_func($this->terminate, $request, $response);
        }
    }
}

