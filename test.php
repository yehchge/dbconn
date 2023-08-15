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

$sql = "INSERT INTO `guestbook` (`id`, `content`, `user`, `created`) 
    VALUES (:id2, :content2, :user2, :created2) 
    ON DUPLICATE KEY 
    UPDATE `id` = :id2, `content` = :content2, `user` = :user2, `created` = :created2";

// $sql = "INSERT INTO `guestbook` (`id`, `content`, `user`, `created`) 
//     VALUES (:id2, :content2, :user2, :created2)";

$data = array(
    'id2' => 9,
    'content2' => 'insertUpdate9',
    'user2' => 'iubody8',
    'created2' => '1997-01-24 00:00:00'  
);
$row = $pdo->select($sql, $data);



// $pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT ); // 預設模式，不主動報錯
// $pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING ); // 引發 E_WARNING 錯誤，主動報錯
// $pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION ); // 主動丟擲 exceptions 異常，需要以try{}cath(){}輸出錯誤資訊。

// // $pdo->setCharset("ancv");
// // $row = $pdo->getRowCount('wrongtable');

// // $res = $pdo->select("SELECT * FROM guestbook WHERE uaser = :user", array('user'=>'joe'));

// // $sth = $pdo->prepare('SELECT skull FROM bones');
// // $sth->execute();
// // $arr = $sth->errorInfo();
// // print_r($arr);
// // exit;

// // $res = $pdo->select("SELECT * FROM guestbook WHERE user = 'joe'");
// // echo "<pre>";print_r($res);
// // 

// $total = $pdo->getRowCount('guestbook');
// $id = $total+1;
// // $dd = array(
// //     'id' => $id,
// //     'content' => 'insertUpdate',
// //     'user' => "iuboy".$id,
// //     'created' => '2021-01-24 00:00:00'
// // );
// // print_r($dd);

// $row = $pdo->insertUpdate('guestbook', array(
//     'id' => $id,
//     'content' => 'insertUpdate',
//     'user' => "iuboy".$id,
//     'created' => '2021-01-24 00:00:00'
// ));
// // echo 2341234;exit;
// // $row = $pdo->row_array("SELECT id FROM guestbook WHERE user = :name", array('name'=>'iuboy'.$id));
// // echo "id = "require './vendor/autoload.php';

// $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
// $dotenv->load();

// echo 'hostname = '.$_ENV['hostname'].PHP_EOL;
// echo 'database = '.$_ENV['database'].PHP_EOL;
// echo 'user = '.$_ENV['user'].PHP_EOL;
// echo 'password = '.$_ENV['password'].PHP_EOL;

// $db = array(
//     'type' => 'mysql',
//     'host' => $_ENV['hostname'],
//     'name' => $_ENV['database'],
//     'user' => $_ENV['user'],
//     'pass' => $_ENV['password']
// );

// $pdo = new DB($db);
// $pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT ); // 預設模式，不主動報錯
// // $pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING ); // 引發 E_WARNING 錯誤，主動報錯
// // $pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION ); // 主動丟擲 exceptions 異常，需要以try{}cath(){}輸出錯誤資訊。

// // $pdo->setCharset("ancv");
// // $row = $pdo->getRowCount('wrongtable');

// // $res = $pdo->select("SELECT * FROM guestbook WHERE uaser = :user", array('user'=>'joe'));

// // $sth = $pdo->prepare('SELECT skull FROM bones');
// // $sth->execute();
// // $arr = $sth->errorInfo();
// // print_r($arr);
// // exit;

// // $res = $pdo->select("SELECT * FROM guestbook WHERE user = 'joe'");
// // echo "<pre>";print_r($res);
// // 

// $total = $pdo->getRowCount('guestbook');
// $id = $total+1;
// $dd = array(
//     'id' => $id,
//     'content' => 'insertUpdate',
//     'user' => "iuboy".$id,
//     'created' => '2021-01-24 00:00:00'
// );
// print_r($dd);

// $row = $pdo->insertUpdate('guestbook', array(
//     'id' => $id,
//     'content' => 'insertUpdate',
//     'user' => "iuboy".$id,
//     'created' => '2021-01-24 00:00:00'
// ));
// echo 2341234;exit;
// $row = $pdo->row_array("SELECT id FROM guestbook WHERE user = :name", array('name'=>'iuboy'.$id));
// echo "id = ". $row['id'];


// $sql = "INSERT INTO `guestbook` (`id`, `content`, `user`, `created`) 
// VALUES (:id2, :content2, :user2, :created2) 
// ON DUPLICATE KEY UPDATE `id`=:id2, `content`=:content2, `user`=:user2, `created`=:created2";

// $row = $pdo->select($sql, array(
//     'id2' => 3,
//     'content2' => 'insertUpdate',
//     'user2' => 'iubody3',
//     'created2' => '2021-01-24 00:00:00',    
// ));
// . $row['id'];



// $db = new PDO('mysql:host=localhost;dbname=myguestbook','root','123456');

// $query = $db->prepare("INSERT INTO `guestbook` (`id`, `content`, `user`, `created`) 
//     VALUES (:id2, :content2, :user2, :created2) 
//     ON DUPLICATE KEY 
//     UPDATE `id`=:id2, `content`=:content2, `user`=:user2, `created`=:created2");


// $id = 8;
// $content = 'insertUpdat8';
// $user = 'iubody8';
// $create = '2028-01-24 00:00:00';
// $query->bindParam(":id2", $id, PDO::PARAM_INT);
// $query->bindParam(":content2", $content, PDO::PARAM_STR);
// $query->bindParam(":user2", $user, PDO::PARAM_STR);
// $query->bindParam(":created2", $create, PDO::PARAM_STR);

// $query->execute();