<?php declare(strict_types=1);

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Paste\Controller;

use Paste\IntegrationTest;

/**
 * @group integration
 */
class IndexControllerTest extends IntegrationTest
{
    /**
     * @test
     */
    public function it_should_return_a_200_response()
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertTrue($client->getResponse()->isOk());
    }
}
