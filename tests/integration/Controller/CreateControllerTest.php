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
class CreateControllerTest extends IntegrationTest
{
    public function test_posting_without_a_body_should_return_a_400(): void
    {
        $client = static::createClient();
        $client->request('POST', '/', [], [], ['HTTP_Accept' => 'text/plain'], '');

        $this->assertEquals(400, $client->getResponse()->getStatusCode());

        $client->request('POST', '/', ['paste' => '']);

        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }

    public function test_posting_a_paste_should_return_the_expected_response_headers(): void
    {
        $client = static::createClient();
        $client->request('POST', '/', [], [], ['HTTP_Accept' => 'text/plain'], 'Lorem ipsum');

        $this->assertEquals(201, $client->getResponse()->getStatusCode());
        $this->assertTrue($client->getResponse()->headers->has('Location'));
        $this->assertTrue($client->getResponse()->headers->has('X-Paste-Id'));
        $this->assertTrue($client->getResponse()->headers->has('X-Paste-Token'));

        $client->request('POST', '/', ['paste' => 'Lorem ipsum', 'redirect' => 'redirect']);

        $this->assertEquals(303, $client->getResponse()->getStatusCode());
        $this->assertTrue($client->getResponse()->headers->has('Location'));
        $this->assertTrue($client->getResponse()->headers->has('X-Paste-Id'));
        $this->assertTrue($client->getResponse()->headers->has('X-Paste-Token'));
    }
}
