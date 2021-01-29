<?php

require './vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo 'hostname = '.$_ENV['hostname'].PHP_EOL;
echo 'database = '.$_ENV['database'].PHP_EOL;
echo 'user = '.$_ENV['user'].PHP_EOL;
echo 'password = '.$_ENV['password'].PHP_EOL;
