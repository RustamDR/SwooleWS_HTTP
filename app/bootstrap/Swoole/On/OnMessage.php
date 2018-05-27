<?php

namespace app\bootstrap\Swoole\On;

/**
 * Class OnMessage
 * @package app\bootstrap\Swoole\On
 */
class OnMessage extends OnBase
{
    /**
     * @param \swoole_websocket_server $server
     * @param $frame
     */
    public function __invoke(\swoole_websocket_server $server, $frame): void
    {
        $fd = $frame->fd;
        $message = json_decode($frame->data, true);

        if (empty($message) || !$message || !isset($message['type'])) {
            $this->log("cmd [NO CMD!]", 'ERROR', $fd);
            return;
        }

        $sockets = $this->sockets();
        $uri = $sockets->getUriByFd($fd);
        $cmd = $message['type'];
        $uri = $uri . '/' . $cmd;
        $resolve = function ($send) use ($sockets, $fd, $cmd, $server) {
            $server->push($fd, $send);
            $this->log("cmd [{$cmd}] => " . json_encode($send), 'info', $fd);
        };
        unset($message['type']);

        try {
            $this->app->wsHandler->handle($uri, array_merge($message, ['fd' => $fd]), $resolve);
        } catch (\Exception|\TypeError|\Error $e) {
            $data = $message ? ' ' . json_encode($message) : '';
            $this->log($e->getMessage() . ': ' . $uri . $data, 'ERROR', $fd);
        }
    }
}