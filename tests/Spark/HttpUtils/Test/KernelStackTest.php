<?php

namespace Spark\HttpUtils\Test;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

use Spark\HttpUtils\CallableKernel;
use Spark\HttpUtils\KernelStack;
use Spark\HttpUtils\Middleware;

class TestMiddleware1 extends Middleware
{
    function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        $resp = $this->app->handle($request, $type, $catch);
        $resp->setContent($resp->getContent() . "\nFoo");

        return $resp;
    }
}

class TestMiddleware2 extends Middleware
{
    function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        $resp = $this->app->handle($request, $type, $catch);
        $resp->setContent($resp->getContent() . "\nBar");

        return $resp;
    }
}

class KernelStackTest extends \PHPUnit_Framework_TestCase
{
    function test()
    {
        $app = new CallableKernel(function(Request $req) {
            return new Response("Hello World");
        });

        $stack = KernelStack::build()
            ->push('\Spark\HttpUtils\Test\TestMiddleware1')
            ->push('\Spark\HttpUtils\Test\TestMiddleware2')
            ->run($app);

        $app = $stack->resolve();

        $resp = $app->handle(new Request);

        $this->assertEquals("Hello World\nBar\nFoo", $resp->getContent());
    }

    function testMap()
    {
        $app = new CallableKernel(function(Request $req) {
            return new Response("Hello World");
        });

        $stack = KernelStack::build()
            ->map('/foo', function($s) {
                $app = new CallableKernel(function() {
                    return new Response("Hello sub!");
                });

                $s->push('\Spark\HttpUtils\Test\TestMiddleware1');
                $s->run($app);
            })
            ->run($app);

        $app = $stack->resolve();

        $response = $app->handle(Request::create('/foo/bar'));
        $this->assertEquals("Hello sub!\nFoo", $response->getContent());
    }
}

