<?php

declare(strict_types=1);

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Paste;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class IntegrationTest extends WebTestCase
{
    public static function createKernel(array $options = []): KernelInterface
    {
        if (isset($options['environment'])) {
            $env = $options['environment'];
        } elseif (isset($_ENV['APP_ENV'])) {
            $env = $_ENV['APP_ENV'];
        } elseif (isset($_SERVER['APP_ENV'])) {
            $env = $_SERVER['APP_ENV'];
        } else {
            $env = 'test';
        }

        if (isset($options['debug'])) {
            $debug = (bool) $options['debug'];
        } elseif (isset($_ENV['APP_DEBUG'])) {
            $debug = (bool) $_ENV['APP_DEBUG'];
        } elseif (isset($_SERVER['APP_DEBUG'])) {
            $debug = (bool) $_SERVER['APP_DEBUG'];
        } else {
            $debug = true;
        }

        return new Kernel($env, $debug);
    }

    public function extractLocationAndToken(Response $response): array
    {
        return [
            $response->headers->get('Location'),
            $response->headers->get('X-Paste-Token'),
        ];
    }
}
