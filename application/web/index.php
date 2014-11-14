<?php

$loader = require_once __DIR__ . '/../vendor/autoload.php';

use Alcohol\Application;
use Symfony\Component\HttpFoundation\Request;

Dotenv::makeMutable();
Dotenv::load(__DIR__ . '/../');
Dotenv::required([
    'SYMFONY_ENV',
    'SYMFONY_DEBUG',
    'SYMFONY__SECRET',
    'SYMFONY__MONOLOG_ACTION_LEVEL',
    'SYMFONY__REDIS__SCHEME',
    'SYMFONY__REDIS__HOST',
    'SYMFONY__REDIS__PORT',
]);

$application = new Application(getenv('SYMFONY_ENV'), getenv('SYMFONY_DEBUG'));
$request = Request::createFromGlobals();
$response = $application->handle($request);
$response->send();
$application->terminate($request, $response);
