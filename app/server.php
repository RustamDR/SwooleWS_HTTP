<?php
require(__DIR__ . '/config/php_ini_set.php');
require(__DIR__ . '/vendor/autoload.php');

$config = require('./config/config.php');
$app = new \app\core\Application\Application($config);
$app->run();

exit(0);