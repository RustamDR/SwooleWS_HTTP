<?php

namespace app\models\repositories\driver;

/**
 *  Хранение в памяти
 * Class MemoryDriver
 * @package app\models\repositories\driver
 */
class MemoryDriver implements IStorage
{
    /** @var array */
    private $data = [];

    /**
     * @inheritdoc
     */
    public function set(string $hash, $object): bool
    {
        $this->data[$hash] = $object;
        return true;
    }

    /**
     * @param string $hash
     * @return mixed|null
     */
    public function get(string $hash)
    {
        return $this->data[$hash] ?? null;
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

    /**
     * @inheritdoc
     */
    public function delete(string $hash): bool
    {
        if ($this->get($hash)) {
            unset($this->data[$hash]);
        }

        return true;
    }
}