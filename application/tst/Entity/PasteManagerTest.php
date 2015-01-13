<?php

namespace Alcohol\PasteBundle\Tests\Entity;

use Alcohol\PasteBundle\Entity\Paste;
use Alcohol\PasteBundle\Entity\PasteManager;

class PasteManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException \LengthException
     * @expectedExceptionCode 413
     */
    public function create_throws_LengthException_for_body_size_larger_than_1MiB()
    {
        $mock = $this->getMockBuilder('Predis\Client')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \Predis\Client $mock */
        $manager = new PasteManager($mock);
        $manager->create(str_repeat('a', 1024 * 1024 + 1));
    }

    /**
     * @test
     */
    public function create_attempts_to_persist_to_Redis_storage()
    {
        $mock = $this->getMockBuilder('Predis\Client')
            ->disableOriginalConstructor()
            ->setMethods(['exists', 'set'])
            ->getMock();

        $mock
            ->expects($this->once())
            ->method('exists')
            ->will($this->returnValue(0));

        $mock
            ->expects($this->once())
            ->method('set')
            ->will($this->returnValue(1));

        /** @var \Predis\Client $mock */
        $manager = new PasteManager($mock);
        $this->assertInstanceOf('Alcohol\PasteBundle\Entity\Paste', $manager->create('dummy'));
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionCode 503
     */
    public function create_throws_RuntimeException_when_Paste_does_not_persist_to_Redis_storage()
    {
        $mock = $this->getMockBuilder('Predis\Client')
            ->disableOriginalConstructor()
            ->setMethods(['exists', 'set'])
            ->getMock();

        $mock
            ->expects($this->once())
            ->method('exists')
            ->will($this->returnValue(0));

        $mock
            ->expects($this->once())
            ->method('set')
            ->will($this->returnValue(0));

        /** @var \Predis\Client $mock */
        $manager = new PasteManager($mock);
        $manager->create('dummy');
    }


    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionCode 403
     */
    public function update_throws_RuntimeException_when_token_does_not_match()
    {
        $mock = $this->getMockBuilder('Predis\Client')
            ->disableOriginalConstructor()
            ->getMock();

        $paste = new Paste('code', 'body', 'token');

        /** @var \Predis\Client $mock */
        $manager = new PasteManager($mock);
        $manager->update($paste, 'dummy');
    }

    /**
     * @test
     */
    public function update_attempts_to_persist_to_Redis_storage()
    {
        $mock = $this->getMockBuilder('Predis\Client')
            ->disableOriginalConstructor()
            ->setMethods(['set'])
            ->getMock();

        $mock
            ->expects($this->once())
            ->method('set')
            ->will($this->returnValue(1));

        $paste = new Paste('code', 'body', 'token');

        /** @var \Predis\Client $mock */
        $manager = new PasteManager($mock, 'token');
        $this->assertEquals($paste, $manager->update($paste, 'token'));
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionCode 503
     */
    public function update_throws_RuntimeException_when_Paste_does_not_persist_to_Redis_storage()
    {
        $mock = $this->getMockBuilder('Predis\Client')
            ->disableOriginalConstructor()
            ->setMethods(['set'])
            ->getMock();

        $mock
            ->expects($this->once())
            ->method('set')
            ->will($this->returnValue(0));

        $paste = new Paste('code', 'body', 'token');

        /** @var \Predis\Client $mock */
        $manager = new PasteManager($mock);
        $manager->update($paste, 'token');
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionCode 403
     */
    public function delete_throws_RuntimeException_when_token_does_not_match()
    {
        $mock = $this->getMockBuilder('Predis\Client')
            ->disableOriginalConstructor()
            ->getMock();

        $paste = new Paste('code', 'body', 'token');

        /** @var \Predis\Client $mock */
        $manager = new PasteManager($mock);
        $manager->delete($paste, 'dummy');
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionCode 503
     */
    public function delete_throws_RuntimeException_when_Paste_cannot_be_deleted_from_Redis_storage()
    {
        $mock = $this->getMockBuilder('Predis\Client')
            ->disableOriginalConstructor()
            ->setMethods(['del'])
            ->getMock();

        $mock
            ->expects($this->once())
            ->method('del')
            ->will($this->returnValue(0));

        $paste = new Paste('code', 'body', 'token');

        /** @var \Predis\Client $mock */
        $manager = new PasteManager($mock);
        $manager->delete($paste, 'token');
    }

    /**
     * @test
     */
    public function delete_attempts_to_remove_Paste_from_Redis_storage()
    {
        $mock = $this->getMockBuilder('Predis\Client')
            ->disableOriginalConstructor()
            ->setMethods(['del'])
            ->getMock();

        $mock
            ->expects($this->once())
            ->method('del')
            ->will($this->returnValue(1));

        $paste = new Paste('code', 'body', 'token');

        /** @var \Predis\Client $mock */
        $manager = new PasteManager($mock);
        $manager->delete($paste, 'token');
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionCode 404
     */
    public function loadPasteByCode_throws_RuntimeException_when_Paste_cannot_be_found_in_Redis_storage()
    {
        $mock = $this->getMockBuilder('Predis\Client')
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $mock
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue(null));

        /** @var \Predis\Client $mock */
        $manager = new PasteManager($mock);
        $manager->loadPasteByCode('code');
    }

    /**
     * @test
     */
    public function loadPasteByCode_returns_Paste_from_Redis_storage()
    {
        $mock = $this->getMockBuilder('Predis\Client')
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $paste = new Paste('code', 'body', 'token');

        $mock
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue(serialize($paste)));

        /** @var \Predis\Client $mock */
        $manager = new PasteManager($mock);
        $this->assertEquals($paste, $manager->loadPasteByCode('code'));
    }
}
