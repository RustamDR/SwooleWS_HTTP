<?php

namespace app\core\Application;

use app\core\Contracts\ILogger;
use Swoole\Exception;

/**
 * Class Application
 * @package app\core
 * @property \swoole_websocket_server $swoole
 * @property \PDO $db
 * @property \Memcached $memcached
 * @property ILogger $logger
 * @property Config $config
 * @property RequestHandler $handler
 * @property WsHandler $wsHandler
 * @property Container $container
 * @property \Credis_Client $redis
 */
class Application
{
    /** @var Config */
    protected $config;

    /** @var \PDO */
    protected $db;

    /** @var \Memcached */
    protected $memcached;

    /** @var \Credis_Client */
    protected $redis;

    /** @var ILogger */
    protected $logger;

    /** @var \swoole_websocket_server */
    protected $swoole;

    /** @var RequestHandler */
    protected $handler;

    /** @var WsHandler */
    protected $wsHandler;

    /** @var Container */
    protected $container;

    /**
     * Application constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = new Config($config);
        $this->handler = new RequestHandler($this);
        $this->wsHandler = new WsHandler($this);
        $this->container = Container::instance();

        $this->init();
        $this->bootstrap();
    }

    /**
     * Initializing
     */
    protected function init(): void
    {
        $this->dbConnect();
        $this->memcachedConnect();
        $this->redisConnect();
        $this->swooleConnect();
    }

    /**
     * Database connection
     */
    protected function dbConnect(): void
    {
        $dbConf = $this->config->get('db');
        if ($dbConf !== false && is_array($dbConf)) {
            $this->db = new \PDO($dbConf['dsn'], $dbConf['username'], $dbConf['password'], [
                //   \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . $dbConf['charset'],
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_TIMEOUT => $dbConf['timeout'] ?? 10,
            ]);
        }
    }

    /**
     * Memcached connection
     */
    protected function memcachedConnect(): void
    {
        $memConf = $this->config->get('memcached');
        if (!is_array($memConf)) {
            throw new Exception('Memcached config error');
        }

        $this->memcached = new \Memcached();
        $this->memcached->addServer($memConf['host'], $memConf['port']);
        if ($memConf['flush']) {
            $this->memcached->flush();
        }
    }

    /**
     * @throws Exception
     */
    protected function redisConnect(): void
    {
        $redisConf = $this->config->get('redis');
        if (!is_array($redisConf)) {
            throw new Exception('Redis config error');
        }

        $this->redis = new \Credis_Client($redisConf['host'], $redisConf['port']);//new RedisClient($redisConf['host'], $redisConf['port']);
        if ($redisConf['flush']) {
            $this->redis->flushAll();
        }
    }

    protected function swooleConnect(): void
    {
        $swConf = $this->config->get('swoole')['websocket'];
        $this->swoole = new \swoole_websocket_server($swConf['server'], $swConf['port'], SWOOLE_BASE);
        $this->swoole->set($swConf['options']);
    }

    /**
     * Run application
     */
    public function run(): void
    {
        $this->swoole->start();
    }

    /**
     * Getter
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        throw new \RuntimeException("Property does not exists ${name}");
    }

    /**
     * Bootstrapping
     */
    protected function bootstrap(): void
    {
        foreach ($this->config->get('bootstrap') as $name) {
            call_user_func(["app\\bootstrap\\{$name}\\Bootstrap", 'execute'], $this);
        }

        $this->logger = Container::get(ILogger::class);
    }
}