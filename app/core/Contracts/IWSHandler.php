<?php

namespace app\core\Contracts;

/**
 * Interface IWSHandler
 * @package app\core\Contracts
 */
interface IWSHandler
{
    /**
     * @param string $uri
     * @param array $message
     * @param callable|null $cb
     */
    public function handle(string $uri, array $message, callable $cb = null): void;
}