<?php

$loader = require_once __DIR__ . '/../vendor/autoload.php';

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
