<?php

namespace Spark\HttpUtils;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Dispatches requests to multiple applications and 
 * returns the first response which is not 404 or 405 (configurable).
 *
 * @author Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 */
class Cascade implements HttpKernelInterface
{
    protected $apps = array();

    /** List of status code which should be not accepted. */
    protected $catch;

    /**
     * Constructor
     *
     * @param HttpKernelInterface $app
     * @param array $apps List of HttpKernelInterface instances
     * @param array $statusCodes Status Codes which don't qualify as 
     * valid response and indicate that the cascade should keep trying.
     */
    function __construct(array $apps, array $statusCodes = array(404, 405))
    {
        foreach ($apps as $app) {
            $this->add($app);
        }

        $this->catch = $statusCodes;
    }

    function add(HttpKernelInterface $app)
    {
        $this->apps[] = $app;

        return $this;
    }

    function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        foreach ($this->apps as $app) {
            $response = $app->handle($request, $type, $catch);

            if (!in_array($response->getStatusCode(), $this->catch)) {
                return $response;
            }
        }

        return new Response("Not Found", 404, array('Content-Type' => 'text/plain'));
    }
}

