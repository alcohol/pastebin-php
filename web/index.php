<?php

$loader = require_once __DIR__ . '/../vendor/autoload.php';

use Alcohol\PasteBundle\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\ClassLoader\ApcClassLoader;
use Symfony\Component\HttpKernel\HttpCache\HttpCache;
use Symfony\Component\HttpKernel\HttpCache\Store;

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

if (in_array(getenv('SYMFONY_ENV'), ['prod']) && extension_loaded('apc')) {
    $apcloader = new ApcClassLoader(sha1(__FILE__), $loader);
    $apcloader->register(true);
}

$application = new Application(getenv('SYMFONY_ENV'), getenv('SYMFONY_DEBUG'));

if (in_array(getenv('SYMFONY_ENV'), ['prod'])) {
    $application->loadClassCache();
    $application = new HttpCache($application, new Store($application->getCacheDir() . '/http'));
}

$request = Request::createFromGlobals();
$response = $application->handle($request);
$response->send();
$application->terminate($request, $response);
