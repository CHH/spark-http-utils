<?php

namespace Spark\HttpUtils;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware which dispatches requests to multiple applications and 
 * returns the first response which is not 404 or 405 (configurable).
 *
 * @author Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 */
class Cascade extends Middleware
{
    protected $apps = array();

    /** List of status code which should be not accepted. */
    protected $statusCodes = array(404, 405);

    /**
     * Constructor
     *
     * @param HttpKernelInterface $app
     * @param array $apps List of HttpKernelInterface instances
     * @param array $statusCodes Status Codes which don't qualify as 
     * valid response and indicate that the cascade should keep trying.
     */
    function __construct(HttpKernelInterface $app, array $apps, array $statusCodes = null)
    {
        parent::__construct($app);

        $this->apps = $apps;

        if (null !== $statusCodes) {
            $this->statusCodes = $statusCodes;
        }
    }

    function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        foreach ($this->apps as $app) {
            $response = $app->handle($request, $type, $catch);

            if (!in_array($response->getStatusCode(), $this->statusCodes)) {
                return $response;
            }
        }

        return $this->app->handle($request, $type, $catch);
    }
}

