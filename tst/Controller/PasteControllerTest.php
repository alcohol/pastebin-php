<?php

namespace Alcohol\PasteBundle\Tests\Controller;

use Alcohol\PasteBundle\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @medium
 */
class PasteControllerTest extends WebTestCase
{
    /**
     * @inheritDoc
     */
    public static function createKernel(array $options = array())
    {
        return new Application(
            isset($options['environment']) ? $options['environment'] : 'test',
            isset($options['debug']) ? $options['debug'] : true
        );
    }

    /**
     * @group functional
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

    /**
     * @group functional
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
    }

    /**
     * @group functional
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
     * @group functional
     */
    public function testPostForm()
    {
        $client = static::createClient();
        $client->request('POST', '/', ['paste' => 'Lorem ipsum']);

        $this->assertEquals(
            201,
            $client->getResponse()->getStatusCode(),
            '"POST /" should return a 201 Created response.'
        );

        $this->assertTrue(
            $client->getResponse()->headers->has('Location'),
            '"POST /" response should include a Location header.'
        );
    }


    /**
     * @group functional
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
