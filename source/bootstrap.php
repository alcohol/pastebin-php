<?php

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use Dotenv\Dotenv;

/** @var Composer\Autoload\ClassLoader $loader */
$loader = require dirname(__DIR__).'/vendor/autoload.php';

$dotenv = new Dotenv(dirname(__DIR__));
$dotenv->load();
$dotenv->required([
    'SYMFONY_ENV',
    'SYMFONY_DEBUG',
    'SYMFONY__SECRET',
]);

return $loader;
