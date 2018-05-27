<?php

namespace app\bootstrap\Swoole\On;

/**
 * Swoole on close callback
 * Class OnClose
 * @package app\bootstrap\Swoole\On
 */
class OnClose extends OnBase
{
    /**
     * @param \swoole_websocket_server $server
     * @param $fd
     */
    public function __invoke(\swoole_websocket_server $server, int $fd): void
    {
        $sockets = $this->sockets();
        $resolve = function (string $log) use ($fd, $sockets) {
            $sockets->deleteUriByFd($fd);
            $this->log($log, 'INFO', $fd);
        };

        $this->app->handler->close($fd, [], $resolve);
    }
}