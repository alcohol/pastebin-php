<?php

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

/** @var Composer\Autoload\ClassLoader $loader */
$loader = require_once __DIR__.'/../source/bootstrap.php';

use Alcohol\Paste\Application;
use Symfony\Component\ClassLoader\ApcClassLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpCache\HttpCache;
use Symfony\Component\HttpKernel\HttpCache\Store;

if (in_array(getenv('SYMFONY_ENV'), ['prod'], true) && extension_loaded('apc')) {
    $apcloader = new ApcClassLoader(sha1(__FILE__), $loader);
    $apcloader->register(true);
    $loader->unregister();
}

$application = new Application(getenv('SYMFONY_ENV'), (bool) getenv('SYMFONY_DEBUG'));

if (in_array(getenv('SYMFONY_ENV'), ['prod'], true)) {
    $application->loadClassCache();
    $application = new HttpCache($application, new Store($application->getCacheDir().'/http'));

    Request::enableHttpMethodParameterOverride();
}

$request = Request::createFromGlobals();
$response = $application->handle($request);
$response->send();

$application->terminate($request, $response);
