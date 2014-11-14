<?php

namespace Alcohol\PasteBundle\Entity;

use Predis\Client;

class PasteManager
{
    /** @var Client */
    protected $redis;

    /** @var integer */
    protected $ttl = 86400;

    /**
     * @param Client $redis
     */
    public function __construct(Client $redis)
    {
        $this->redis = $redis;
    }

    /**
     * @param integer $ttl
     */
    public function setTtl($ttl = 86400)
    {
        $this->ttl = $ttl;
    }

    /**
     * @param string $body
     * @return Paste|boolean
     */
    public function create($body)
    {
        do {
            $code = uniqid();
        } while ($this->redis->exists($code));

        if (function_exists('openssl_random_pseudo_bytes')) {
            $token = bin2hex(openssl_random_pseudo_bytes(22));
        } elseif (function_exists('mcrypt_create_iv')) {
            $token = bin2hex(mcrypt_create_iv(22));
        } else {
            $token = bin2hex(file_get_contents('/dev/urandom', null, null, 0, 22));
        }

        $paste = new Paste($code, $body, $token);

        return $this->persist($paste, 'NX') ? $paste : false;
    }

    /**
     * @param Paste $paste
     * @param string $flag
     * @return boolean
     */
    public function persist(Paste $paste, $flag = 'XX')
    {
        return (boolean) $this->redis->set('paste:' . $paste->getCode(), serialize($paste), 'EX', $this->ttl, $flag);
    }

    /**
     * @param Paste $paste
     * @return boolean
     */
    public function delete(Paste $paste)
    {
        return (boolean) $this->redis->del(array('paste:' . $paste->getCode()));
    }

    /**
     * @param string $code
     * @return Paste|boolean
     */
    public function loadPasteByCode($code)
    {
        $result = $this->redis->get('paste:' . $code);

        if (null === $result) {
            return false;
        }

        $paste = unserialize($result);

        return $paste;
    }
}
