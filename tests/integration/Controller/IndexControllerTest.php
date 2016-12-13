<?php

declare(strict_types=1);

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace AppBundle\Controller;

use Paste\IntegrationTest;

/**
 * @group integration
 */
class IndexControllerTest extends IntegrationTest
{
    /**
     * @testdox Index page should render.
     */
    public function testIndex()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertTrue(
            $client->getResponse()->isOk(),
            '"GET /" should return a 200 OK response.'
        );

        $this->assertGreaterThan(
            0,
            $crawler->filter('a')->count(),
            '"GET /" response should contain at least one link.'
        );
    }
}
