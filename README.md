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

$app = new CallableKernel(function(Request $req) {
    return new Response("Hello from app!");
});

$app = new MyFilter($app);
$app->handle(Request::createFromGlobals())->send();
# Output:
# Hello from app!
# Hello from filter!
```

### `KernelStack`

### `CallableKernel`

## Thanks

Thank you [Igor Wiedler][] for writing a [Blog Post][] about composing
HttpKernels.

[Blog Post]: https://igor.io/2013/02/02/http-kernel-middlewares.html
[Igor Wiedler]: https://igor.io

