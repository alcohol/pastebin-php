<?php

declare(strict_types=1);

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
    public function __construct(string $code, string $body)
    {
        $this->setCode($code);
        $this->setBody($body);
    }

    /**
     * @param string $code
     */
    protected function setCode(string $code)
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getCode(): string
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
     * @return array
     */
    public function __sleep(): array
    {
        return ['code', 'body'];
    }
}
