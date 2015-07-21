<?php

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

$loader = require dirname(__DIR__) . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = new Dotenv(dirname(__DIR__));
$dotenv->load();
$dotenv->required([
    'SYMFONY_ENV',
    'SYMFONY_DEBUG',
    'SYMFONY__SECRET',
]);

return $loader;
