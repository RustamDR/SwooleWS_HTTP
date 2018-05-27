<?php

namespace app\bootstrap\Singletons;

use app\components\logger\DummyLogger;
use app\core\Application\Application;
use app\core\Contracts\IBootstrap;
use app\core\Contracts\ILogger;
use app\models\repositories\driver\MemcachedDriver;
use app\models\repositories\SocketUriRepository;
use app\models\Sockets;

/**
 * Class Registry
 * @package app\bootstrap\Singletons
 */
class Bootstrap implements IBootstrap
{
    /**
     * @inheritdoc
     */
    public static function execute(Application $app)
    {
        // HERE ANY BOOTSTRAP FOR MODELS AND SO ON...
        $container = $app->container;

        // Logger должен быть забустраплен первым (пока так)
        $container->setSingleton(ILogger::class, new DummyLogger());

        $sockets = new Sockets(
            new SocketUriRepository(new MemcachedDriver($app->memcached, 'socket'))
        );
        $container->setSingleton(Sockets::class, $sockets);
    }
}