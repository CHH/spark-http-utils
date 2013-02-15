<?php

require(__DIR__ . '/../../vendor/autoload.php');

use Symfony\Component\HttpFoundation\Request;

$req = Request::createFromGlobals();

$app = require(__DIR__ . '/app.php');

$resp = $app->handle($req);
$resp->send();

$app->terminate($req, $resp);

