<?php

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Alcohol\PasteBundle\Tests\Entity;

use Alcohol\PasteBundle\Entity\Paste;
use LengthException;

class PasteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @testdox Make sure all the getters and setters behave as expected.
     */
    public function testSettersGetters()
    {
        $paste = new Paste('code', 'body', 'token');

        $this->assertEquals('code', $paste->getCode());
        $this->assertEquals('body', $paste->getBody());
        $this->assertEquals('token', $paste->getToken());

        $paste->setCode('foo');
        $paste->setBody('bar');
        $paste->setToken('baz');

        $this->assertEquals('foo', $paste->getCode());
        $this->assertEquals('bar', $paste->getBody());
        $this->assertEquals('baz', $paste->getToken());
    }

    /**
     * @testdox When no/empty body is given, a LengthException is thrown.
     * @expectedException LengthException
     */
    public function testMinLengthException()
    {
        new Paste('code', '', 'token');
    }

    /**
     * @testdox When trying to set a body that exceeds 1MiB, a LengthException is thrown.
     * @expectedException LengthException
     */
    public function testMaxLengthException()
    {
        new Paste('code', str_repeat('s', 1024 * 1024 + 1), 'token');
    }
}
