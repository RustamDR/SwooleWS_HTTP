<?php

namespace app\components\async\redis;

/**
 * Redis Async
 * https://github.com/swoole/redis-async/blob/master/src/Swoole/Async/RedisClient.php
 *
 * Class Redis
 *
 * @method void sAdd(string $key, string $data, callable $cb)
 * @method void sRem(string $key, string $data, callable $cb)
 * @method void set(string $key, string $data, callable $cb)
 * @method void del(string $key, callable $cb)
 * @method mixed get(string $key, callable $cb)
 * @method void watch(string $key, callable $cb)
 * @method void multi(callable $cb)
 * @method void exec(callable $cb)
 * @method void flushall(callable $cb)
 * @method select
 * @method hexists
 * @method sMembers(string $key,callable $cb)
 * @package Swoole\Async
 */
class RedisClient
{
    /** @var string */
    public $host;
    /** @var int */
    public $port;
    /** @var bool */
    public $debug = true;

    /**
     * @var array
     */
    public $pool = array();

    /**
     * RedisClient constructor.
     * @param string $host
     * @param int $port
     * @param float $timeout
     */
    public function __construct(string $host = 'localhost', int $port = 6379, float $timeout = 0.1)
    {
        $this->host = $host;
        $this->port = $port;
    }

    /**
     * Debug message
     * @param string $msg
     */
    function trace(string $msg): void
    {
        echo "-----------------------------------------\n" . trim($msg) . "\n-----------------------------------------\n";
    }

    /**
     * @return string
     */
    function stats(): string
    {
        return 'Idle connection: ' . count($this->pool) . "<br />\n";
    }

    /**
     * @param $key
     * @param array $value
     * @param $callback
     */
    function hmset(string $key, array $value, callable $callback): void
    {
        $lines[] = "hmset";
        $lines[] = $key;
        foreach ($value as $k => $v) {
            $lines[] = $k;
            $lines[] = $v;
        }
        $connection = $this->getConnection();
        $cmd = $this->parseRequest($lines);
        $connection->command($cmd, $callback);
    }

    /**
     * @param $key
     * @param array $value
     * @param $callback
     */
    function hmget(string $key, array $value, callable $callback): void
    {
        $connection = $this->getConnection();
        $connection->fields = $value;

        array_unshift($value, "hmget", $key);
        $cmd = $this->parseRequest($value);
        $connection->command($cmd, $callback);
    }

    /**
     * @param $array
     * @return string
     */
    function parseRequest(array $array): string
    {
        $cmd = '*' . count($array) . "\r\n";
        foreach ($array as $item) {
            $cmd .= '$' . strlen($item) . "\r\n" . $item . "\r\n";
        }
        return $cmd;
    }

    /**
     * @param string $method
     * @param array $args
     */
    public function __call(string $method, array $args): void
    {
        $callback = array_pop($args);
        array_unshift($args, $method);
        $cmd = $this->parseRequest($args);
        $connection = $this->getConnection();
        $connection->command($cmd, $callback);
    }

    /**
     * @return RedisConnection
     */
    protected function getConnection(): RedisConnection
    {
        if (count($this->pool) > 0) {
            /**
             * @var $connection RedisConnection
             */
            foreach ($this->pool as $k => $connection) {
                unset($this->pool[$k]);
                break;
            }
            return $connection;
        } else {
            return new RedisConnection($this);
        }
    }

    /**
     * @param int $id
     */
    function lockConnection(int $id): void
    {
        unset($this->pool[$id]);
    }

    /**
     * @param $id
     * @param RedisConnection $connection
     */
    function freeConnection(int $id, RedisConnection $connection): void
    {
        $this->pool[$id] = $connection;
    }
}

/**
 * Class RedisConnection
 * @package app\components\async\redis
 */
class RedisConnection
{
    /** @var RedisClient */
    protected $redis;
    /** @var string */
    protected $buffer = '';
    /** @var \swoole_client */
    protected $client;
    /** @var callable */
    protected $callback;

    /** @var bool */
    protected $wait_send = false;
    /** @var bool */
    protected $wait_recv = false;
    /** @var bool */
    protected $multi_line = false;
    /** @var array[] */
    public $fields;

    /**
     * RedisConnection constructor.
     * @param RedisClient $redis
     */
    function __construct(RedisClient $redis)
    {
        $client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);
        $client->on('connect', array($this, 'onConnect'));
        $client->on('error', array($this, 'onError'));
        $client->on('receive', array($this, 'onReceive'));
        $client->on('close', array($this, 'onClose'));
        $client->connect($redis->host, $redis->port);
        $this->client = $client;
        $redis->pool[$client->sock] = $this;
        $this->redis = $redis;
    }

    function clean(): void
    {
        $this->buffer = '';
        $this->callback;
        $this->wait_send = false;
        $this->wait_recv = false;
        $this->multi_line = false;
        $this->fields = array();
    }

    /**
     * @param $cmd
     * @param $callback
     */
    function command(string $cmd, callable $callback): void
    {
        // Если уже подключены, отпавим данные напрямую
        if ($this->client->isConnected()) {
            $this->client->send($cmd);
        } else {
            //Не подключен, ожидая соединения для отправки данных
            $this->wait_send = $cmd;
        }
        $this->callback = $callback;
        // Удалим из пула пустых соединений, чтобы не использовать другие задачи
        $this->redis->lockConnection($this->client->sock);
    }

    /**
     * @param \swoole_client $client
     */
    function onConnect(\swoole_client $client): void
    {
        if ($this->wait_send) {
            $client->send($this->wait_send);
            $this->wait_send = '';
        }
    }

    function onError(): void
    {
        echo "Redis server connection error\n";
    }

    /**
     * @param $cli
     * @param $data
     */
    function onReceive($cli, $data): void
    {
        $success = true;
        if ($this->redis->debug) {
            $this->redis->trace($data);
        }
        if ($this->wait_recv) {
            $this->buffer .= $data;
            if ($this->multi_line) {
                $require_line_n = $this->multi_line * 2 + 1 - substr_count($data, "$-1\r\n");
                if (substr_count($this->buffer, "\r\n") - 1 == $require_line_n) {
                    goto parse_multi_line;
                } else {
                    return;
                }
            } else {
                //Готово
                if (strlen($this->buffer) >= $this->wait_recv) {
                    $result = rtrim($this->buffer, "\r\n");
                    goto ready;
                } else {
                    return;
                }
            }
        }

        $lines = explode("\r\n", $data, 2);
        $type = $lines[0][0];
        if ($type == '-') {
            $success = false;
            $result = substr($lines[0], 1);
        } elseif ($type == '+') {
            $result = substr($lines[0], 1);;
        } //Только одна строка данных
        elseif ($type == '$') {
            $len = intval(substr($lines[0], 1));
            if ($len > strlen($lines[1])) {
                $this->wait_recv = $len;
                $this->buffer = $lines[1];
                $this->multi_line = false;
                return;
            }
            $result = $lines[1];
        } // Многострочные данные
        elseif ($type == '*') {
            parse_multi_line:
            $data_line_num = intval(substr($lines[0], 1));
            $data_lines = explode("\r\n", $lines[1]);
            $require_line_n = $data_line_num * 2 - substr_count($data, "$-1\r\n");
            $lines_n = count($data_lines) - 1;

            if ($lines_n == $require_line_n) {
                $result = array();
                $key_n = 0;
                for ($i = 0; $i < $lines_n; $i++) {
                    //not exists
                    if (substr($data_lines[$i], 1, 2) === '-1') {
                        $value = false;
                    } else {
                        $value = $data_lines[$i + 1];
                        $i++;
                    }
                    if ($this->fields) {
                        $result[$this->fields[$key_n]] = $value;
                    } else {
                        $result[] = $value;
                    }
                    $key_n++;
                }
                goto ready;
            } //Недостаточно данных, нужен кеш
            else {
                $this->multi_line = $data_line_num;
                $this->buffer = $lines[1];
                $this->wait_recv = true;
                return;
            }
        } elseif ($type == ':') {
            $result = intval(substr($lines[0], 1));
            goto ready;
        } else {
            echo "Response is not a redis result. String:\n$data\n";
            return;
        }

        ready:
        $this->clean();
        $this->redis->freeConnection($cli->sock, $this);
        call_user_func($this->callback, $result, $success);
    }

    /**
     * @param \swoole_client $cli
     */
    function onClose(\swoole_client $cli): void
    {
        if ($this->wait_send) {
            $this->redis->freeConnection($cli->sock, $this);
            call_user_func($this->callback, "timeout", false);
        }
    }
}