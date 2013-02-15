<?php

use Silex\Application;
use Spark\HttpUtils\Stack;

$app = new Application;

$app->get('/', function() {
    return "Hello World!";
});

$stack = new Stack;
$stack->map('^/foo', function($stack) {
    $app = new Application;

    $app->get('/', function() {
        return "Hello from sub app!";
    });

    $app->finish(function() {
        echo "Sub app finished!";
    });

    return $app;
});

$app->finish(function() {
    echo "Root app finished!";
});

return $stack->resolve($app);
