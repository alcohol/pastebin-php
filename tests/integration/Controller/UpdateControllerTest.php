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
 *
 * @internal
 */
final class UpdateControllerTest extends IntegrationTest
{
    public function testItShouldReturnA400IfPasteExistsButAuthenticationHeaderIsMissing(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $client->request('POST', '/', [], [], [], 'Lorem ipsum');
        [$location, /* $token */] = $this->extractLocationAndToken($client->getResponse());
        $client->request('PUT', $location, [], [], [], 'Ipsum lorem');

        static::assertSame(400, $client->getResponse()->getStatusCode());
    }

    public function testItShouldReturnA404IfPasteExistsButAuthenticationHeaderIsInvalid(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $client->request('POST', '/', [], [], [], 'Lorem ipsum');
        [$location, /* $token */] = $this->extractLocationAndToken($client->getResponse());
        $client->request('PUT', $location, [], [], ['HTTP_X-Paste-Token' => 'dummy-token'], 'Ipsum lorem');

        static::assertSame(404, $client->getResponse()->getStatusCode());
    }

    public function testItShouldReturnA404IfPasteDoesNotExistButAuthenticationHeaderIsGiven(): void
    {
        $client = static::createClient();
        $client->request('PUT', '/dummy', [], [], ['HTTP_X-Paste-Token' => 'dummy-token'], 'Ipsum lorem');

        static::assertSame(404, $client->getResponse()->getStatusCode());
    }

    public function testItShouldReturnA204IfPasteExistsAndValidAuthenticationHeaderIsGiven(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $client->request('POST', '/', [], [], [], 'Lorem ipsum');
        [$location, $token] = $this->extractLocationAndToken($client->getResponse());
        $client->request('PUT', $location, [], [], ['HTTP_X-Paste-Token' => $token], 'Ipsum lorem');

        static::assertSame(204, $client->getResponse()->getStatusCode());

        $client->request('GET', $location, [], [], ['HTTP_Accept' => 'text/plain']);

        static::assertSame('Ipsum lorem', $client->getResponse()->getContent());
    }
}
