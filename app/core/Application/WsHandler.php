<?php

namespace app\core\Application;

use app\core\Contracts\IWSHandler;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;
use Swoole\Exception;

/**
 * Websocket command handler execute
 * Class WsHandler
 * @package app\core\Application
 */
class WsHandler implements IWSHandler
{
    /** @var Application */
    protected $app;

    /** @var Dispatcher */
    protected $dispatcher;

    /**
     * WsHandler constructor.
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $routes = require(__DIR__ . '/../../routes/websocket.php');
        $dispatchCallback = function (RouteCollector $r) use ($routes) {
            foreach ($routes as $route => $commands) {
                foreach ((array)$commands as $command => $handler)
                    $r->addRoute('GET', $route . '/' . $command, $handler);
            }
        };
        $this->dispatcher = simpleDispatcher($dispatchCallback);
    }

    /**
     * @param string $uri
     * @param array $message
     * @param callable|null $resolve
     * @throws Exception
     */
    public function handle(string $uri, array $message, callable $resolve = null): void
    {
        $route = $this->dispatcher->dispatch('GET', $uri);

        switch ($route[0]) {

            case Dispatcher::NOT_FOUND:
                throw new Exception('Bad command type');
                break;

            case Dispatcher::METHOD_NOT_ALLOWED:
                throw new Exception('Command not allowed');
                break;

            case Dispatcher::FOUND:
                $handler = $route[1];
                $vars = array_merge($route[2], ['fd' => $message['fd']]);
                $handler(new RequestParams($vars), $message, $resolve);
                break;
        }
    }
}