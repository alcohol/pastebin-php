<?php

namespace Alcohol\PasteBundle\Tests\Entity;

use Alcohol\PasteBundle\Entity\Paste;

class PasteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function all_setters_and_getters_behave_as_expected()
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
     * @test
     * @expectedException \LengthException
     */
    public function setBody_throws_LengthException_when_body_size_is_larger_than_1MiB()
    {
        new Paste('code', str_repeat('s', 1024 * 1024 + 1), 'token');
    }
}
