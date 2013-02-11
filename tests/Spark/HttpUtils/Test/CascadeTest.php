<?php

namespace Spark\HttpUtils\Test;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Spark\HttpUtils\CallableKernel;
use Spark\HttpUtils\Cascade;

class CascadeTest extends \PHPUnit_Framework_TestCase
{
    function testCascade()
    {
        $cascade = new Cascade(array(
            new CallableKernel(function() {
                return new Response("Handler1", 404);
            }),
            new CallableKernel(function() {
                return new Response("Handler2", 200);
            })
        ));

        $resp = $cascade->handle(Request::create('/'));

        $this->assertEquals("Handler2", $resp->getContent());
    }

    function testConfigureStatusCodes()
    {
        $cascade = new Cascade(array(
            new CallableKernel(function() {
                return new Response("Handler1", 404);
            }),
            new CallableKernel(function() {
                return new Response("Handler2", 200);
            })
        ), array(200));

        $resp = $cascade->handle(Request::create('/'));

        $this->assertEquals("Handler1", $resp->getContent());
    }
}

