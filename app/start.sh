#!/bin/bash

composer install
mkdir -p ./logs
php server.php 1>>./logs/info.log 2>>./logs/error.log