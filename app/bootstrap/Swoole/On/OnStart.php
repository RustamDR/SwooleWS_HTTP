<?php

namespace app\bootstrap\Swoole\On;

/**
 * Class OnStart
 * @package app\bootstrap\Swoole\On
 */
class OnStart extends OnBase
{
    /**
     * @param \swoole_websocket_server $server
     */
    public function __invoke(\swoole_websocket_server $server): void
    {
        $serverInfo = "Start on {$server->host}:{$server->port}: success";
        $_ = str_repeat('-', strlen($serverInfo));
        $this->log($_);
        $this->log($serverInfo);
        $this->log($_);
    }
}