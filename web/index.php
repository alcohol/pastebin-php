<?php

declare(strict_types=1);

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use Alcohol\Paste\AppCache;
use Alcohol\Paste\AppKernel;
use Symfony\Component\ClassLoader\ApcClassLoader;
use Symfony\Component\HttpFoundation\Request;

/** @var Composer\Autoload\ClassLoader $loader */
$loader = require_once __DIR__ . '/../source/bootstrap.php';

if (in_array(getenv('SYMFONY_ENV'), ['prod'], true) && extension_loaded('apc')) {
    $apcloader = new ApcClassLoader(sha1(__FILE__), $loader);
    $apcloader->register(true);
    $loader->unregister();
}

$kernel = new AppKernel(getenv('SYMFONY_ENV'), (bool) getenv('SYMFONY_DEBUG'));

if (in_array(getenv('SYMFONY_ENV'), ['prod'], true)) {
    $kernel->loadClassCache();
    $kernel = new AppCache($kernel);

    Request::enableHttpMethodParameterOverride();
}

$request = Request::createFromGlobals();

$response = $kernel->handle($request);
$response->send();

$kernel->terminate($request, $response);
