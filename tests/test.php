<?php

require __DIR__.'/../src/DB.php';

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

$total = $pdo->getRowCount('guestbook');
$id = $total+1;
$row = $pdo->insert('guestbook', array(
    'id' => $id,
    'content' => 'Good Insert',
    'user' => "boy{$id}",
    'created' => '2021-01-24 00:00:00'

));


$result = $pdo->showColumns('users');
echo "<pre>";print_r($result);
