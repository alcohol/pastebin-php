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
     * @return Paste
     * @throws \RuntimeException
     */
    public function create($body)
    {
        do {
            $code = $this->getHash();
        } while ($this->redis->exists($code));

        $token = $this->getHash(10);
        $paste = new Paste($code, $body, $token);

        return $this->persist($paste, 'NX');
    }

    /**
     * @param Paste $paste
     * @param string $token
     * @return Paste
     * @throws \RuntimeException
     */
    public function update(Paste $paste, $token)
    {
        if (!hash_equals($token, $paste->getToken())) {
            throw new \RuntimeException('Unable to persist paste to storage, invalid token.', 403);
        }

        return $this->persist($paste);
    }

    /**
     * @param Paste $paste
     * @param string $token
     * @return boolean
     * @throws \RuntimeException
     */
    public function delete(Paste $paste, $token)
    {
        if (!hash_equals($token, $paste->getToken())) {
            throw new \RuntimeException('Unable to delete paste from storage, invalid token.', 403);
        }

        if (!$this->redis->del(array('paste:' . $paste->getCode()))) {
            throw new \RuntimeException('Unable to delete paste from storage.', 503);
        }

        return true;
    }

    /**
     * @param string $code
     * @return Paste
     * @throws \RuntimeException
     */
    public function loadPasteByCode($code)
    {
        $paste = $this->redis->get('paste:' . $code);

        if (null === $paste) {
            throw new \RuntimeException('Paste not found: ' . $code, 404);
        }

        $paste = unserialize($paste);

        return $paste;
    }

    /**
     * @param Paste $paste
     * @param string $flag
     * @return Paste
     * @throws \RuntimeException
     */
    protected function persist(Paste $paste, $flag = 'XX')
    {
        if (!$this->redis->set('paste:' . $paste->getCode(), serialize($paste), 'EX', $this->ttl, $flag)) {
            throw new \RuntimeException('Unable to persist paste to storage.', 503);
        }

        return $paste;
    }

    /**
     * @param integer $length
     * @return string
     */
    protected function getHash($length = 4)
    {
        return bin2hex(file_get_contents('/dev/urandom', null, null, 0, $length / 2));
    }
}
