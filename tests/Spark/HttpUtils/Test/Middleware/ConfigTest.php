<?php

namespace Spark\HttpUtils\Test;

use Symfony\Component\HttpFoundation\Request;
use Spark\HttpUtils\Middleware\Config;
use Spark\HttpUtils\CallableKernel;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    function test()
    {
        $self = $this;

        $app = new CallableKernel(function($req) use ($self) {
            $self->assertEquals("bar", $req->attributes->get('foo'));
        });

        $app = new Config($app, array('foo' => 'bar'));
        $app->handle(Request::create('/'));
    }
}

