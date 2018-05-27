<?php

namespace app\core\Contracts;

/**
 * Interface IHandler
 * @package app\core\Contracts
 */
interface IHandler
{
    /**
     * @param string $method
     * @param string $uri
     * @param array $params
     * @param \swoole_http_request $request
     * @param callable|null $cb
     */
    public function handle(string $method, string $uri, array $params = [], \swoole_http_request $request, callable $cb = null): void;

    /**
     * @param int $fd
     * @param array $params
     * @param callable|null $resolve
     */
    public function close(int $fd, array $params, callable $resolve = null): void;
}