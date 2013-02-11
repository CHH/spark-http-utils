<?php

namespace Spark\HttpUtils;

use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Convenient builder for decorating objects implementing HttpKernelInterface
 */
class KernelStack
{
    /**
     * List of middleware specs, which consist of a class name
     * and optional constructor arguments.
     */
    protected $middlewares;

    /**
     * Contains mappings from prefix to HttpKernelInterface, which is used
     * by the UrlMap Middleware Component
     */
    protected $map = array();

    /**
     * The original app, which gets run last.
     */
    protected $app;

    static function build()
    {
        $stack = new static;
        return $stack;
    }

    function __construct()
    {
        $this->middlewares = new \SplStack;
    }

    /**
     * Pushes a middleware component onto the stack.
     *
     * Middleware components are specified as a class name, and then followed
     * by any number of constructor arguments.
     *
     * Example:
     *
     *     <?php
     *
     *     $foo = new CallableKernel(function($req) {
     *         return new Response("Foo!");
     *     });
     *
     *     $stack = KernelStack::build()
     *         ->push("\\Spark\\HttpUtils\\UrlMap", array('/foo' => $foo))
     *         ->run(new CallableKernel(function($req) {
     *              return new Response("Hello World");
     *         });
     *
     * @return KernelStack
     */
    function push()
    {
        $spec = func_get_args();
        $this->middlewares->push($spec);

        return $this;
    }

    function run(HttpKernelInterface $app)
    {
        $this->app = $app;

        return $this;
    }

    /**
     * Maps a path to an app.
     *
     * If called with a callable as second argument, then the callable is passed a new
     * instance of the KernelStack, which can be used to set middleware and the app which
     * gets used if the `$path` is matched. If the second argument is an instance of HttpKernelInterface,
     * then this app gets run when the `$path` is matched.
     *
     * @param string $path Pattern for matching the path.
     * @param HttpKernelInterface|callable
     * @return KernelStack
     */
    function map($path, $block)
    {
        if ($block instanceof HttpKernelInterface) {
            $app = $block;
        } elseif (is_callable($block)) {
            $stack = new static;
            $block($stack);
            $app = $stack->resolve();
        }

        $this->map[$path] = $app;

        return $this;
    }

    function resolve()
    {
        $app = $this->app;

        if ($app === null) {
            throw new \UnexpectedValueException('No app set. Ensure you have called the run() method');
        }

        foreach ($this->middlewares as $spec) {
            $kernelClass = array_shift($spec);
            array_unshift($spec, $app);

            $r = new \ReflectionClass($kernelClass);
            $app = $r->newInstanceArgs($spec);
        }

        if ($this->map) {
            $app = new UrlMap($app, $this->map);
        }

        return $app;
    }
}

