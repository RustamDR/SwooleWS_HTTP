<?php

namespace app\models\repositories\driver;

/**
 * Sync Redis Driver
 * Class RedisSyncDriver
 * @package app\models\repositories\driver
 */
class RedisSyncDriver implements IStorage
{
    /** @var \Credis_Client */
    private $redis;
    /** @var string */
    private $collectionName;

    /**
     * RedisAsyncDriver constructor.
     * @param \Credis_Client $redis
     * @param $collectionName
     */
    public function __construct(\Credis_Client $redis, string $collectionName = '')
    {
        $this->redis = $redis;
        $this->collectionName = $collectionName;
    }

    /**
     * @param string $hash
     * @param string $object
     * @return bool
     */
    public function set(string $hash, string $object): bool
    {
        return $this->redis->set($this->collectionKey($hash), $object);
    }

    /**
     * @param string $hash
     * @return null|string
     */
    public function get(string $hash): ?string
    {
        return $this->redis->get($this->collectionKey($hash));
    }

    /**
     * @param string $hash
     * @return bool
     */
    public function delete(string $hash): bool
    {
        return $this->delete($this->collectionKey($hash));
    }

    /**
     * @param string $hash
     * @param string $val
     * @return array
     */
    public function setAdd(string $hash, string $val): array
    {
        $key = $this->collectionKey($hash);
        $res = $this->redis
            ->pipeline()
            ->watch($key)
            ->multi()
            ->sAdd($key, $val)
            ->sMembers($key)
            ->exec();

        return $res[1];
    }

    /**
     * @param string $hash
     * @param string $val
     * @return array|null
     */
    public function setRemove(string $hash, string $val): ?array
    {
        $key = $this->collectionKey($hash);
        $res = $this->redis->pipeline()
            ->watch($key)
            ->multi()
            ->sRem($key, $val)
            ->sMembers($key)
            ->exec();

        return $res[1];
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
}