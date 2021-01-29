<?php

$_ENV['UNIQID'] = 'fly';
putenv("UNIQID=C564f5a46sdf8z9sd8f4a9");
echo "ddd = ".$_ENV['UNIQID'].PHP_EOL;
echo print_r($_ENV);exit;

echo 'My username is ' .$_ENV["USER"] . '!';
