<?php

namespace app\bootstrap\Swoole;

use app\bootstrap\Swoole\On\OnHandshake;
use app\bootstrap\Swoole\On\OnStart;
use app\core\Application\Application;
use app\core\Contracts\IBootstrap;
use app\bootstrap\Swoole\On\OnClose;
use app\bootstrap\Swoole\On\OnMessage;
use app\bootstrap\Swoole\On\OnOpen;
use app\bootstrap\Swoole\On\OnRequest;

/**
 * Class Bootstrap
 * @package app\bootstrap\Swoole
 */
class Bootstrap implements IBootstrap
{
    /**
     * @param Application $app
     */
    public static function execute(Application $app): void
    {
        $app->swoole->on('start', new OnStart($app));

        $app->swoole->on('handshake', new OnHandshake($app));
        //$app->swoole->on('open', new OnOpen($app));

        $app->swoole->on('request', new OnRequest($app));
        $app->swoole->on('message', new OnMessage($app));

        $app->swoole->on('close', new OnClose($app));
    }
}