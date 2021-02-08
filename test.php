<?php

require './vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo 'hostname = '.$_ENV['hostname'].PHP_EOL;
echo 'database = '.$_ENV['database'].PHP_EOL;
echo 'user = '.$_ENV['user'].PHP_EOL;
echo 'password = '.$_ENV['password'].PHP_EOL;

$db = array(
    'type' => 'mysql',
    'host' => $_ENV['hostname'],
    'name' => $_ENV['database'],
    'user' => $_ENV['user'],
    'pass' => $_ENV['password']
);

$pdo = new DB($db);
$pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT ); // 預設模式，不主動報錯
// $pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING ); // 引發 E_WARNING 錯誤，主動報錯
// $pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION ); // 主動丟擲 exceptions 異常，需要以try{}cath(){}輸出錯誤資訊。

// $pdo->setCharset("ancv");
// $row = $pdo->getRowCount('wrongtable');

// $res = $pdo->select("SELECT * FROM guestbook WHERE uaser = :user", array('user'=>'joe'));

// $sth = $pdo->prepare('SELECT skull FROM bones');
// $sth->execute();
// $arr = $sth->errorInfo();
// print_r($arr);
// exit;

// $res = $pdo->select("SELECT * FROM guestbook WHERE user = 'joe'");
// echo "<pre>";print_r($res);
// 

$total = $pdo->getRowCount('guestbook');
$id = $total+1;
$row = $pdo->insertUpdate('guestbook', array(
    'id' => $id,
    'content' => 'insertUpdate',
    'user' => "iuboy".$id,
    'created' => '2021-01-24 00:00:00'
));
echo 2341234;exit;
$row = $pdo->row_array("SELECT id FROM guestbook WHERE user = :name", array('name'=>'iuboy'.$id));
echo "id = ". $row['id'];