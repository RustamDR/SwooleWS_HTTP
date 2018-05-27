<?php

return [

    //*/
    'db' => false,
    /*/'db' => [
        'dsn' => 'mysql:dbname=' . getenv('MYSQL_DB') . ';host:' . getenv('MYSQL_HOST'),
        'username' => getenv('MYSQL_USER'),
        'password' => getenv('MYSQL_PASS'),
        'charset' => 'utf8',
        'timeout' => 10,
    ],
    //*/

    'memcached' => [
        'host' => getenv('MEMCACHE_HOST'),
        'port' => (int)getenv('MEMCACHE_PORT'),
        'flush' => true,
    ],

    'redis' => [
        'host' => getenv('REDIS_HOST'),
        'port' => (int)getenv('REDIS_PORT'),
        'flush' => true,
    ],

    'swoole' => [
        'websocket' => [
            'server' => getenv('LISTEN_IP') ?? '0.0.0.0',
            'port' => 3000,
            'options' => [
                'worker_num' => 25,
                'max_connection' => 250000,
                'max_request' => 10000000,
            ]
        ]
    ],

    'bootstrap' => require(__DIR__ . '/bootstrap.php'),
];