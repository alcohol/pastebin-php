<?php

namespace Alcohol\PasteBundle\Entity;

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
     */
    public function __construct($code, $body, $token)
    {
        $this->code = $code;
        $this->body = $body;
        $this->token = $token;
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
     */
    public function setBody($body)
    {
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

    /**
     * @return string
     */
    public function __toString()
    {
        return json_encode(['_type' => 'paste'] + get_object_vars($this), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
