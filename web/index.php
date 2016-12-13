<?php

declare(strict_types=1);

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use Paste\AppCache;
use Paste\AppKernel;
use Symfony\Component\HttpFoundation\Request;

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require_once __DIR__ . '/../app/bootstrap.php';
$kernel = new AppKernel(getenv('SYMFONY_ENV'), (bool) getenv('SYMFONY_DEBUG'));

if (in_array(getenv('SYMFONY_ENV'), ['prod'], true)) {
    $kernel = new AppCache($kernel);

    Request::enableHttpMethodParameterOverride();
}

$request = Request::createFromGlobals();

$response = $kernel->handle($request);
$response->send();

$kernel->terminate($request, $response);
