<?php

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

$loader = require_once __DIR__.'/../vendor/autoload.php';

Dotenv::load(__DIR__.'/../');
Dotenv::required([
    'SYMFONY_ENV',
    'SYMFONY_DEBUG',
    'SYMFONY__SECRET',
]);
