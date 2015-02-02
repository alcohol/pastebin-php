<?php

namespace Alcohol\PasteBundle\Entity;

use Alcohol\PasteBundle\Exception\StorageException;
use Alcohol\PasteBundle\Exception\TokenException;
use Alcohol\PasteBundle\Util\HashUtils;
use Predis\Client;

class PasteManager
{
    /** @var Client */
    protected $redis;

    /** @var HashUtils */
    protected $hash;

    /** @var integer */
    protected $ttl = 86400;

    /**
     * @param Client $redis
     * @param HashUtils $hash
     */
    public function __construct(Client $redis, HashUtils $hash)
    {
        $this->redis = $redis;
        $this->hash = $hash;
    }

    /**
     * @param integer $ttl
     */
    public function setTtl($ttl = 86400)
    {
        $this->ttl = $ttl;
    }

    /**
     * @return integer
     */
    public function getTtl()
    {
        return $this->ttl;
    }

    /**
     * @param string $body
     * @return Paste
     * @throws StorageException
     */
    public function create($body)
    {
        do {
            $code = $this->hash->generate();
        } while ($this->redis->exists('paste:' . $code));

        $token = $this->hash->generate(10);
        $paste = new Paste($code, $body, $token);

        return $this->persist($paste, 'NX');
    }

    /**
     * @param string $code
     * @return Paste
     * @throws StorageException
     */
    public function read($code)
    {
        $paste = $this->redis->get('paste:' . $code);

        if (null === $paste) {
            throw new StorageException('Paste not found: ' . $code);
        }

        $paste = unserialize($paste);

        return $paste;
    }

    /**
     * @param Paste $paste
     * @param string $token
     * @return Paste
     * @throws TokenException
     * @throws StorageException
     */
    public function update(Paste $paste, $token)
    {
        if (!$this->hash->compare($token, $paste->getToken())) {
            throw new TokenException('Unable to persist paste to storage, invalid token.');
        }

        return $this->persist($paste);
    }

    /**
     * @param Paste $paste
     * @param string $token
     * @return boolean
     * @throws TokenException
     * @throws StorageException
     */
    public function delete(Paste $paste, $token)
    {
        if (!$this->hash->compare($token, $paste->getToken())) {
            throw new TokenException('Unable to delete paste from storage, invalid token.');
        }

        if (!$this->redis->del(array('paste:' . $paste->getCode()))) {
            throw new StorageException('Unable to delete paste from storage.');
        }

        return true;
    }

    /**
     * @param Paste $paste
     * @param string $flag
     * @return Paste
     * @throws StorageException
     */
    protected function persist(Paste $paste, $flag = 'XX')
    {
        if (!$this->redis->set('paste:' . $paste->getCode(), serialize($paste), 'EX', $this->getTtl(), $flag)) {
            throw new StorageException('Unable to persist paste to storage.');
        }

        return $paste;
    }
}
