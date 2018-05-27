<?php

namespace app\models\repositories\driver;

/**
 * Interface IAsyncStorage
 * @package app\models\repositories\driver
 */
interface IAsyncStorage
{
    /**
     * @param string $hash
     * @param $object
     * @param callable $cb
     */
    public function set(string $hash, $object, callable $cb): void;

    /**
     * @param string $hash
     * @param callable $cb
     * @return mixed
     */
    public function get(string $hash, callable $cb);

    /**
     * @param string $hash
     * @param string $val
     * @param callable $cb
     * @return mixed
     */
    public function setAdd(string $hash, string $val, callable $cb);

    /**
     * @param string $hash
     * @param string $val
     * @param callable $cb
     * @return mixed
     */
    public function setRemove(string $hash, string $val, callable $cb);

    /**
     * @param string $hash
     * @param callable $cb
     */
    public function delete(string $hash, callable $cb): void;

    /**
     * @param string $hash
     * @param callable $cb
     */
    public function transaction(string $hash, callable $cb): void;
}