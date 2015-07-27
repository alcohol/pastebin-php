<?php

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Alcohol\Paste\Entity;

class Paste
{
    /** @var string */
    protected $code;

    /** @var string */
    protected $body;

    /**
     * @param string $code
     * @param string $body
     */
    public function __construct($code, $body)
    {
        $this
            ->setCode($code)
            ->setBody($body)
        ;
    }

    /**
     * @param string $code
     *
     * @return $this
     */
    protected function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $body
     *
     * @return $this
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->body;
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        return ['code', 'body'];
    }
}
