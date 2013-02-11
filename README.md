# Spark HttpUtils

_Utilities for composing HttpKernels_

## Install

Get [composer]:

    $ wget http://getcomposer.org/composer.phar

Install `spark/http-utils` package:

    $ php composer.phar require spark/http-utils:*@dev

[composer]: http://getcomposer.org

## Get Started

### What is a Middleware Component?

A Middleware Component is a class that decorates an object implementing
the [HttpKernelInterface][]. The term "Middleware" originates from
[Rack][], a Ruby Web Server Interface similar to PHP's builtin SAPI.

[Rack]: http://rack.github.com/
[HttpKernelInterface]: http://api.symfony.com/2.1/Symfony/Component/HttpKernel/HttpKernelInterface.html

By convention a Middleware component in HttpUtils is a class which takes
an object implementing [HttpKernelInterface][] as an argument to the
constructor and implements the [HttpKernelInterface][] by itself.

```php
<?php

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;

class MyMiddleware implements HttpKernelInterface
{
    protected $app;

    function __construct(HttpKernelInterface $app)
    {
        $this->app = $app;
    }

    function handle(Request $request, $type = HttpKernel::MASTER_REQUEST, $catch = true)
    {
    }
}
```

The `Spark\HttpUtils\Middleware` base class is provided for convenience.

```php
<?php

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;

class MyMiddleware extends \Spark\HttpUtils\Middleware
{
    function handle(Request $request, $type = HttpKernel::MASTER_REQUEST, $catch = true)
    {
        # App is in $this->app
    }
}
```

Because writing Middleware components which intercept requests, or
intercept responses is a common use case, a `Spark\HttpUtils\Filter`
base class is also provided for convenience.

```php
<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MyFilter extends \Spark\HttpUtils\Filter
{
    function before(Request $request)
    {
    }

    function after(Request $request, Response $response)
    {
        $response->setContent(
            $response->getContent() . "\n" . "Hello from filter!"
        );
    }
}
```

You can already use this to composer middlewares and applications which
implement the [HttpKernelInterface][]:

```php
<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Spark\HttpUtils\CallableKernel;

$app = new CallableKernel(function(Request $req) {
    return new Response("Hello from app!");
});

$app = new MyFilter($app);
$app->handle(Request::createFromGlobals())->send();
# Output:
# Hello from app!
# Hello from filter!
```

This is a bit unconvenient, but works. For more convenience a `KernelBuilder` class is provided.

### `KernelBuilder`

The KernelBuilder provides a more convenient API to compose objects implementing the [HttpKernelInterface][].

Middleware components can be added by calling the `push` method. The previous example for composing middlewares
could be rewritten, using the KernelBuilder, in the following way:

```php
<?php

use Spark\HttpUtils\KernelBuilder;

$builder = new KernelBuilder;
$builder->push('MyFilter');
$builder->run($app);

$app = $builder->resolve();
```

The app passed to `run` is always used as the first element in the chain of middleware components. So
requests flow downward towards the application, while responses bubble upwards from the application. 

The KernelBuilder can also be used to map sub paths to `HttpKernelInterface` instances. This kernels then
receive the path sans the sub path as their request's path info and request URI. The original values can still
be retrieved via the `spark.url_map.original_pathinfo` and `spark.url_map.original_pathinfo` request attributes.

The sub path in which the app is mapped to can be retrieved via the `spark.url_map.path` request attribute.

```php
<?php

$foo = new CallableKernel(function($req) {
    return new Response(sprintf(
        "Hello from sub app at '%s'!", $req->attributes->get('spark.url_map.path')
    ));
});

$builder->map('/foo', $foo);
```

The sub paths can also make use of middleware components by using `map` with a callback, which gets
passed a fresh builder.

```php
$builder->map('/foo', function($builder) {
    $builder->push("MyFilter");
    
    $builder->run(new CallableKernel(function($req) {
        return new Response("Hello from sub app!");
    });
});
```

### `CallableKernel`

The `Spark\HttpUtils\CallableKernel` class makes a plain callback into an app which implements
the [HttpKernelInterface][]. This is very useful for testing, or writing simple apps.

```php
<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Spark\HttpUtils\CallableKernel;

$handle = function(Request $req) {
    return new Response("Hello World");
};

$app = new CallableKernel($handle);
```

The CallableKernel also implements the `TerminableInterface`. To register a callback for the `terminate`
method call, pass a callable as second argument to the constructor.

```php
<?php

$terminate = function(Request $req, Response $resp) {
    # Gets run when the app's `terminate` method gets called.
};

$app = new CallableKernel($handle, $terminate);
```

## Thanks

Thank you [Igor Wiedler][] for writing a [Blog Post][] about composing
HttpKernels.

[Blog Post]: https://igor.io/2013/02/02/http-kernel-middlewares.html
[Igor Wiedler]: https://igor.io

