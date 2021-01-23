<?php

require __DIR__.'/../src/DB.php';

echo DB::world();

$database = "myguestbook";;
$user = 'root';
$password = '123456';
$db = array(
    'type' => 'mysql',
    'host' => 'localhost',
    'name' => $database,
    'user' => $user,
    'pass' => $password
);
$pdo = new DB($db);

echo "count=".$pdo->getRowCount('guestbook');