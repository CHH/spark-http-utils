<?php

namespace Spark\HttpUtils\Test;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Spark\HttpUtils\CallableKernel;
use Spark\HttpUtils\UrlMap;

class UrlMapTest extends \PHPUnit_Framework_TestCase
{
    function test()
    {
        $app = new CallableKernel(function(Request $req) {
            return new Response("Fallback!");
        });

        $urlMap = new UrlMap($app);
        $urlMap->setMap(array(
            '/foo' => new CallableKernel(function(Request $req) {
                return new Response('foo');
            })
        ));

        $req = Request::create('/foo');
        $resp = $urlMap->handle($req);

        $this->assertEquals('foo', $resp->getContent());
    }

    function testOverridesPathInfo()
    {
        $test = $this;

        $app = new CallableKernel(function(Request $req) {
            return new Response("Fallback!");
        });

        $urlMap = new UrlMap($app);
        $urlMap->setMap(array(
            '/foo' => new CallableKernel(function(Request $req) use ($test) {
                $test->assertEquals('/foo', $req->attributes->get('spark.url_map.original_pathinfo'));
                $test->assertEquals('/foo?bar=baz', $req->attributes->get('spark.url_map.original_request_uri'));
                $test->assertEquals('/', $req->getPathinfo());

                return new Response("Hello World");
            })
        ));

        $req = Request::create('/foo?bar=baz');
        $resp = $urlMap->handle($req);

        $this->assertEquals('Hello World', $resp->getContent());
    }
}

