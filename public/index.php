<?php declare(strict_types=1);

use Paste\Kernel;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;

require __DIR__ . '/../vendor/autoload.php';

// The check is to ensure we don't use .env in production
if (false === getenv('APP_ENV')) {
    if (!class_exists(Dotenv::class)) {
        throw new \RuntimeException('APP_ENV environment variable is not defined. You need to define environment variables for configuration or add "symfony/dotenv" as a Composer dependency to load variables from a .env file.');
    }

    (new Dotenv())->load(__DIR__ . '/../.env');
}

$env = getenv('APP_ENV') ?? 'dev';
$debug = (bool) (getenv('APP_DEBUG') ?? ('prod' !== $env));
$kernel = new Kernel($env, $debug);

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
