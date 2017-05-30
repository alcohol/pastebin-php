<?php declare(strict_types=1);

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Paste\Entity;

final class Paste implements \Serializable
{
    /**
     * @var string
     */
    private $code;
    /**
     * @var string
     */
    private $body;

    /**
     * @param string $body
     *
     * @return \Paste\Entity\Paste
     */
    public static function create($body)
    {
        $paste = new self();
        $paste->body = $body;

        return $paste;
    }

    /**
     * @param string $code
     *
     * @return \Paste\Entity\Paste
     */
    public function persist($code)
    {
        $paste = new self();
        $paste->code = $code;
        $paste->body = $this->body;

        return $paste;
    }

    /**
     * @param string $body
     *
     * @return \Paste\Entity\Paste
     */
    public function update($body)
    {
        $paste = new self();
        $paste->code = $this->code;
        $paste->body = $body;

        return $paste;
    }

    /**
     * @return string|null
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return string|null
     */
    public function getBody()
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
