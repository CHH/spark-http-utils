<?php

namespace Spark\HttpUtils\Middleware;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;

class Config extends Filter
{
    protected $config;

    function __construct(HttpKernelInterface $app, array $config)
    {
        parent::__construct($app);

        $this->config = $config;
    }

    function before(Request $request)
    {
        $request->attributes->add($this->config);
    }
}

