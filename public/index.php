<?php declare(strict_types=1);

use Paste\Kernel;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;

require __DIR__ . '/../vendor/autoload.php';

$env = getenv('APP_ENV') ?? 'dev';
$debug = (bool) (getenv('APP_DEBUG') ?? ('prod' !== $env));
$kernel = new Kernel($env, $debug);

if ($debug && class_exists(Debug::class)) {
    Debug::enable();
}

if ($kernel->isProduction()) {
    Request::setTrustedProxies([
        '10.0.0.0/8',
        '172.16.0.0/12',
        '192.168.0.0/16',
        '127.0.0.1',
    ], Request::HEADER_X_FORWARDED_ALL);

    Request::setTrustedHosts([
        '^php.pastie.eu$',
    ]);
}

$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->headers->set('X-Pastebin-Version', $_SERVER['SENTRY_RELEASE'] ?? 'development');
$response->headers->set('X-Container-Php', gethostname());
$response->send();
$kernel->terminate($request, $response);
