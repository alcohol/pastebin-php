<?php

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Alcohol\Paste\Tests\Integration;

use Alcohol\Paste\Application;
use Predis\Collection\Iterator\Keyspace;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class IntegrationTest extends WebTestCase
{
    public static function createKernel(array $options = array())
    {
        return new Application(
            isset($options['environment']) ? $options['environment'] : 'test',
            isset($options['debug']) ? $options['debug'] : true
        );
    }

    public static function tearDownAfterClass()
    {
        $kernel = self::createKernel();
        $kernel->boot();
        /** @var \Predis\Client $predis */
        $predis = $kernel->getContainer()->get('predis.client');

        foreach (new Keyspace($predis, 'paste:*') as $key) {
            $predis->del([$key]);
        }
    }
}
