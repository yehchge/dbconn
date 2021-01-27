<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class DBA
{
    public function getRowCount($table)
    {
        $pdo = new PDO("mysql:host=".$_ENV['hostname'].";dbname=".$_ENV['database'],$_ENV['user'],$_ENV['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            $res = $pdo->query("SELECT COUNT(1) FROM $table");
            $count = $res->fetchColumn();
            return $count;
        } catch (PDOException $e) {
            die($e->getMessage().PHP_EOL);
        }
    }
}

class MyTest extends TestCase
{
    public function testPDOException() : void
    {
        $pdo = new DBA();
        
        // Assert
        $this->expectException(PDOException::class);

        // Act
        $row = $pdo->getRowCount('notable');
    }
}

// $dd = new DBA();
// echo $dd->getRowCount('guestbook');
