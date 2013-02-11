<?php

namespace Spark\HttpUtils\Test;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Spark\HttpUtils\CallableKernel;

class CallableKernelTest extends \PHPUnit_Framework_TestCase
{
    function testHandleCallback()
    {
        $kernel = new CallableKernel(function(Request $req) {
            return new Response("Hello World");
        });

        $resp = $kernel->handle(Request::create('/'));
        $this->assertEquals("Hello World", $resp->getContent());
    }

    function testTeminate()
    {
        $kernel = new CallableKernel(
            function(Request $req) {
                return new Response("Hello World");
            },
            function(Request $req, Response $resp) use (&$called) {
                $called = true;
            }
        );

        $req = Request::create('/');
        $resp = $kernel->handle($req);

        $this->assertEquals("Hello World", $resp->getContent());

        $kernel->terminate($req, $resp);

        $this->assertTrue($called);
    }
}
