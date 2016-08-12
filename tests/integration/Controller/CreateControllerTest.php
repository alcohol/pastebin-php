<?php

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Alcohol\Paste\Controller;

use Alcohol\Paste\IntegrationTest;

/**
 * @group integration
 */
class CreateControllerTest extends IntegrationTest
{
    /**
     * @testdox Posting a paste as raw body content should return the correct response headers.
     */
    public function testPostRaw()
    {
        $client = static::createClient();
        $client->request('POST', '/', [], [], [], 'Lorem ipsum');

        $this->assertEquals(
            201,
            $client->getResponse()->getStatusCode(),
            '"POST /" should return a 201 Created response.'
        );

        $this->assertTrue(
            $client->getResponse()->headers->has('Location'),
            '"POST /" response should include a Location header.'
        );

        $this->assertTrue(
            $client->getResponse()->headers->has('X-Paste-Id'),
            '"POST /" response should include a X-Paste-Id header.'
        );

        $this->assertTrue(
            $client->getResponse()->headers->has('X-Paste-Token'),
            '"POST /" response should include a X-Paste-Token header.'
        );
    }

    /**
     * @testdox Posting an empty raw body should return a 400 Bad Request.
     */
    public function testPostRawFail()
    {
        $client = static::createClient();
        $client->request('POST', '/', [], [], [], '');

        $this->assertEquals(
            400,
            $client->getResponse()->getStatusCode(),
            '"POST /" without input should return a 400 Bad Request.'
        );
    }

    /**
     * @testdox Posting a paste as form field content should return the correct response headers.
     */
    public function testPostForm()
    {
        $client = static::createClient();
        $client->request('POST', '/', ['paste' => 'Lorem ipsum', 'redirect' => 'redirect']);

        $this->assertEquals(
            303,
            $client->getResponse()->getStatusCode(),
            '"POST /" should return a 303 See Other response.'
        );

        $this->assertTrue(
            $client->getResponse()->headers->has('Location'),
            '"POST /" response should include a Location header.'
        );

        $this->assertTrue(
            $client->getResponse()->headers->has('X-Paste-Id'),
            '"POST /" response should include a X-Paste-Id header.'
        );

        $this->assertTrue(
            $client->getResponse()->headers->has('X-Paste-Token'),
            '"POST /" response should include a X-Paste-Token header.'
        );
    }

    /**
     * @testdox Posting an empty form field should return a 400 Bad Request.
     */
    public function testPostFormFail()
    {
        $client = static::createClient();
        $client->request('POST', '/', ['paste' => '']);

        $this->assertEquals(
            400,
            $client->getResponse()->getStatusCode(),
            '"POST /" without input should return a 400 Bad Request.'
        );
    }
}
