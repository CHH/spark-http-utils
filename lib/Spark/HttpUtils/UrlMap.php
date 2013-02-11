<?php

namespace Spark\HttpUtils;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcher;

/**
 * URL Map Middleware, which maps kernels to paths
 *
 * Maps kernels to path prefixes is insertable into a pipeline.
 *
 * @author Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 */
class UrlMap extends Middleware
{
    protected $map = array();

    function __construct(HttpKernelInterface $app, array $map = array())
    {
        parent::__construct($app);

        if ($map) {
            $this->setMap($map);
        }
    }

    function setMap(array $map)
    {
        # Sort paths by their length descending, so the most specific
        # paths go first.
        uksort($map, function($a, $b) {
            $lenA = strlen($a);
            $lenB = strlen($b);

            if ($lenA < $lenB) {
                return 1;
            } else if ($lenA === $lenB) {
                return 0;
            } else if ($lenA > $lenB) {
                return -1;
            }
        });

        $this->map = $map;
    }

    function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        foreach ($this->map as $path => $app) {
            $matcher = new RequestMatcher;
            $matcher->matchPath($path);

            if ($matcher->matches($request)) {
                $originalRequestUri = $request->getRequestUri();

                $newRequest = Request::create(
                    substr(rawurldecode($originalRequestUri), strlen($path)),
                    $request->getMethod(),
                    $request->query->all(),
                    $request->cookies->all(),
                    $request->files->all(),
                    $request->server->all(),
                    $request->getContent()
                );

                $newRequest->attributes->add($request->attributes->all());

                $newRequest->attributes->set('spark.url_map.path', $path);
                $newRequest->attributes->set('spark.url_map.original_pathinfo', $request->getPathInfo());
                $newRequest->attributes->set('spark.url_map.original_request_uri', $request->getRequestUri());

                return $app->handle($newRequest, $type, $catch);
            }
        }

        return $this->app->handle($request, $type, $catch);
    }
}

