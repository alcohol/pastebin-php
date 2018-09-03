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
class HealthControllerTest extends IntegrationTest
{
    public function test_it_should_return_a_200_response_for_HEAD_requests(): void
    {
        $client = static::createClient();
        $client->request('HEAD', '/health');

        $this->assertTrue($client->getResponse()->isOk());
    }

    public function test_it_should_return_a_200_response_for_GET_requests(): void
    {
        $client = static::createClient();
        $client->request('GET', '/health');

        $this->assertTrue($client->getResponse()->isOk());
    }
}
