<?php

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Alcohol\Paste;

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
}
