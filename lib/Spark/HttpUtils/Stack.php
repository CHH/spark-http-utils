<?php

namespace Spark\HttpUtils;

use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Convenient builder for decorating objects implementing HttpKernelInterface
 */
class Stack
{
    /**
     * List of middleware specs, which consist of a class name
     * and optional constructor arguments.
     *
     * @var \SplStack
     */
    protected $middlewares;

    /**
     * Contains mappings from prefix to HttpKernelInterface, which is used
     * by the UrlMap Middleware Component
     */
    protected $map = array();

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
     *     $app = new CallableKernel(function($req) {
     *         return new Response("Root App!");
     *     });
     *
     *     $sub = new CallableKernel(function($req) {
     *         return new Response("Sub App!");
     *     });
     *
     *     $stack = Stack::build()
     *         ->push("\\Spark\\HttpUtils\\UrlMap", array('^/foo' => $sub));
     *      
     *     $app = $stack->resolve($app);
     *
     * @return KernelBuilder
     */
    function push()
    {
        $spec = func_get_args();
        $this->middlewares->push($spec);

        return $this;
    }

    /**
     * Maps a path to an app.
     *
     * If called with a callable as second argument, then the callable is passed a new
     * instance of the Stack, which can be used to set middleware and the app which
     * gets used if the `$path` is matched. The block must return an app.
     *
     * If the second argument is an instance of HttpKernelInterface,
     * then this app gets run when the `$path` is matched.
     *
     * @param string $path Pattern for matching the path.
     * @param HttpKernelInterface|callable $block
     * @return KernelBuilder
     */
    function map($path, $block)
    {
        if ($block instanceof HttpKernelInterface) {
            $app = $block;
        } elseif (is_callable($block)) {
            $stack = new static;
            $app = $block($stack);

            if (!$app instanceof HttpKernelInterface) {
                throw new \UnexpectedValueException("Callable must return an instance of HttpKernelInterface");
            }
        }

        $this->map[$path] = $app;

        return $this;
    }

    /**
     * Resolves the configured middleware component specs in LIFO order, instantiates them
     * and returns the last middleware component.
     *
     * @return HttpKernelInterface
     */
    function resolve(HttpKernelInterface $app)
    {
        $middlewares = array($app);

        foreach ($this->middlewares as $spec) {
            $kernelClass = array_shift($spec);
            array_unshift($spec, $app);

            $r = new \ReflectionClass($kernelClass);
            $app = $r->newInstanceArgs($spec);

            array_unshift($middlewares, $app);
        }

        if ($this->map) {
            $app = new UrlMap($app, $this->map);
        }

        return new StackedHttpKernel($app, $middlewares);
    }
}

