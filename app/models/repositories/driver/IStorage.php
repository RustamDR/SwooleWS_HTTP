<?php

namespace app\models\repositories\driver;

/**
 * Interface IStorage
 * @package app\models\repositories\driver
 */
interface IStorage
{
    /**
     * @param string $hash
     * @param $object
     * @return bool
     */
    public function set(string $hash, string $object): bool;

    /**
     * @param string $hash
     * @return mixed
     */
    public function get(string $hash);

    /**
     * @param string $hash
     * @return bool
     */
    public function delete(string $hash): bool;

    /**
     * @param string $hash
     * @param string $val
     * @return mixed
     */
    public function setAdd(string $hash, string $val);

    /**
     * @param string $hash
     * @param string $val
     * @return mixed
     */
    public function setRemove(string $hash, string $val);
}