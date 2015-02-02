<?php

namespace Alcohol\PasteBundle\Entity;

use LengthException;

class Paste
{
    /** @var string */
    protected $code;

    /** @var string */
    protected $body;

    /** @var string */
    protected $token;

    /**
     * @param string $code
     * @param string $body
     * @param string $token
     * @throws \LengthException
     */
    public function __construct($code, $body, $token)
    {
        $this->setCode($code);
        $this->setBody($body);
        $this->setToken($token);
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
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
     * @throws LengthException
     */
    public function setBody($body)
    {
        $size = ini_get('mbstring.func_overload') ? mb_strlen($body, '8bit') : strlen($body);

        if ($size > 1024 * 1024) {
            throw new LengthException('Maximum string size of 1MiB exceeded.');
        }

        $this->body = $body;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param mixed $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        return ['code', 'body', 'token'];
    }
}
