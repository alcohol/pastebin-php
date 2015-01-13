<?php

namespace Alcohol\PasteBundle\Tests\Entity;

use Alcohol\PasteBundle\Entity\Paste;

class PasteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException \LengthException
     */
    public function setBody_throws_LengthException_when_body_size_is_larger_than_1MiB()
    {
        new Paste('code', str_repeat('s', 1024 * 1024 + 1), 'token');
    }
}
