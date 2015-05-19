<?php

$loader = require_once __DIR__ . '/../vendor/autoload.php';

Dotenv::load(__DIR__ . '/../');
Dotenv::required([
    'SYMFONY_ENV',
    'SYMFONY_DEBUG',
    'SYMFONY__SECRET',
]);
