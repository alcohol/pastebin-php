<?php

namespace Alcohol\PasteBundle\Tests\Controller;

use Alcohol\PasteBundle\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

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

    public function testIndex()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertTrue($client->getResponse()->isOk(), 'Index page should return a 200 OK.');
        $this->assertGreaterThan(0, $crawler->filter('a')->count(), 'There should be at least one link.');
    }
}
