<?php

require __DIR__.'/../src/DB.php';

$database = $_ENV['database'];
$user = $_ENV['user'];
$password = $_ENV['password'];
$db = array(
    'type' => 'mysql',
    'host' => $_ENV['hostname'],
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
