<?php declare(strict_types=1);

require __DIR__.'/../src/DB.php';

use PHPUnit\Framework\TestCase;
use PHPUnit\DbUnit\TestCaseTrait;

class DBTest extends TestCase
{
    use TestCaseTrait;

    /**
     * @covers \DB
     */
    public function test_world(): void
    {
        $this->assertEquals('Hello World'.PHP_EOL, DB::world());
    }

    // protected function setUp(): void
    // {
    //     $database = "myguestbook";;
    //     $user = 'root';
    //     $password = '123456';
    //     $db = array(
    //         'type' => 'mysql',
    //         'host' => 'localhost',
    //         'name' => $database,
    //         'user' => $user,
    //         'pass' => $password
    //     );
    //     $pdo = new DB($db);

    //     $pdo->exec('CREATE TABLE IF NOT EXISTS guestbook (id int, content text, uesr text, created text)');
    //     $pdo->exec("use $database");
    //     $this->getDataSet();
    // }




    public function getConnection()
    {
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

        $pdo->exec('CREATE TABLE IF NOT EXISTS guestbook (id int, content text, user text, created text)');
        return $this->createDefaultDBConnection($pdo, $database);
    }

    public function getDataSet()
    {
        return $this->createFlatXMLDataSet(__DIR__.'/dataSets/myFlatXmlFixture.xml');
    }

    /**
     * @covers \DB
     */
    public function testGetRowCount()
    {
        $this->assertEquals(2, $this->getConnection()->getRowCount('guestbook'));
    }

}