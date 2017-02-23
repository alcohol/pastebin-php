<?php declare(strict_types=1);

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Paste\Entity;

class Paste implements \Serializable
{
    /** @var string */
    protected $code;

    /** @var string */
    protected $body;

    /**
     * @param string $body
     */
    public function __construct(string $body)
    {
        $this->setBody($body);
    }

    /**
     * @param string $code
     */
    public function setCode(string $code)
    {
        $this->code = $code;
    }

    /**
     * @return string|null
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $body
     */
    public function setBody(string $body)
    {
        $this->body = $body;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->body;
    }

    /**
     * @return string
     */
    public function serialize(): string
    {
        return serialize([$this->code, $this->body]);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        list($this->code, $this->body) = unserialize($serialized);
    }
}
