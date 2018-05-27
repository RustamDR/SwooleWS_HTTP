<?php

namespace app\models\repositories\driver;

/**
 * Хранение в memcached
 * Class MemcachedDriver
 * @package app\models\repositories\driver
 */
class MemcachedDriver implements IStorage
{
    /** @var \Memcached */
    private $memcached;
    /** @var string */
    private $collectionName;

    /**
     * MemcachedDriver constructor.
     * @param \Memcached $memcached
     * @param string $collectionName
     */
    public function __construct(\Memcached $memcached, $collectionName = '')
    {
        $this->memcached = $memcached;
        $this->collectionName = $collectionName;
    }

    /**
     * @inheritdoc
     */
    public function set(string $key, $object): bool
    {
        return $this->memcached->set($this->collectionKey($key), serialize($object));
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function get(string $key)
    {
        $result = unserialize($this->memcached->get($this->collectionKey($key)));
        return $result ? $result : null;
    }

    /**
     * @inheritdoc
     */
    public function delete(string $key): bool
    {
        return $this->memcached->delete($this->collectionKey($key));
    }

    /**
     * @param string $key
     * @return string
     */
    private function collectionKey(string $key): string
    {
        if ($key === '') {
            throw new \MemcachedException('Empty key');
        }

        return $this->collectionName . $key;
    }

    /**
     * @inheritdoc
     */
    public function setAdd(string $hash, string $val)
    {
    }

    /**
     * @inheritdoc
     */
    public function setRemove(string $hash, string $val)
    {
    }
}