<?php declare(strict_types=1);

require __DIR__.'/../src/DB.php';

use PHPUnit\Framework\TestCase;

class DBTest extends TestCase
{

    protected function setUp(): void
    {
        // PDO
        $pdo = $this->init();

        $pdo->exec('CREATE TABLE IF NOT EXISTS guestbook (id int not null AUTO_INCREMENT, content text, user text, created text, PRIMARY KEY(id))');
        $pdo->exec('CREATE TABLE IF NOT EXISTS users (id int not null AUTO_INCREMENT, username text, PRIMARY KEY (id))');
        $pdo->exec('use '.$_ENV['database']);
        $pdo->exec('TRUNCATE guestbook');
        $pdo->exec('INSERT INTO guestbook(id,content,user,created) VALUE (1,"Hello buddy!","joe","2010-04-24 17:15:23")');
        $pdo->exec('INSERT INTO guestbook(id,content,user,created) VALUE (2,"I like it!","nancy","2010-04-26 12:14:20")');
    }


    # --------------------------- PDO ---------------------------

    public function init()
    {
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
        return $pdo;
    }

    // public function getConnection()
    // {
    //     $database = $_ENV['database'];
    //     $pdo = $this->init();
    //     $pdo->exec('CREATE TABLE IF NOT EXISTS guestbook (id int, content text, user text, created text)');
    //     $pdo->exec('CREATE TABLE IF NOT EXISTS users (id int not null AUTO_INCREMENT, username text, PRIMARY KEY (id))');
    //     return $this->createDefaultDBConnection($pdo, $database);
    // }

    // public function getDataSet()
    // {
    //     return $this->createFlatXMLDataSet(__DIR__.'/dataSets/myFlatXmlFixture.xml');
    // }

    /**
     * @covers \DB
     */
    public function testGetRowCount()
    {
        $pdo = $this->init();
        $count = $pdo->getRowCount('guestbook');
        $this->assertEquals(2, $count);
    }

    /**
     * @covers \DB
     */
    public function testSelect()
    {
        $pdo = $this->init();
        $row = $pdo->row_array("SELECT id FROM guestbook WHERE user = :name", array('name'=>'joe'));
        $this->assertEquals(1, $row['id']);
    }

    /**
     * @covers \DB
     */
    public function testInsert()
    {
        $pdo = $this->init();
        $total = $pdo->getRowCount('guestbook');
        $id = $total+1;
        $row = $pdo->insert('guestbook', array(
            'id' => $id,
            'content' => 'Good Insert',
            'user' => "boy{$id}",
            'created' => '2021-01-24 00:00:00'
        ));
        $row = $pdo->row_array("SELECT id FROM guestbook WHERE user = :name", array('name'=>'boy'.$id));
        $this->assertEquals($id, $row['id']);
    }

    /**
     * @covers \DB
     */
    public function testExec_insert()
    {
        $pdo = $this->init();
        $total = $pdo->getRowCount('guestbook');
        $id = $total+1;
        $row = $pdo->exec_insert('guestbook', array(
            'id' => $id,
            'content' => 'exec_nsert',
            'user' => "execboy{$id}",
            'created' => '2021-01-24 00:00:00'
        ));
        $row = $pdo->row_array("SELECT id FROM guestbook WHERE user = :name", array('name'=>'execboy'.$id));
        $this->assertEquals($id, $row['id']);
    }

    /**
     * @covers \DB
     */
    public function testUpdate()
    {
        $pdo = $this->init();
        $total = $pdo->getRowCount('guestbook');
        $id = $total+1;
        $data = array(
            'content' => 'iLoveFish'           
        );
        $where = "id = :id";
        $result = $pdo->update('guestbook', $data, $where, array('id'=>2));
        $this->assertEquals(true, $result);
    }

    /**
     * @covers \DB
     */
    public function testDelete(){
        $pdo = $this->init();
        $where = 'id = :id';
        $result = $pdo->delete('guestbook', $where, array('id'=>2));
        $this->assertEquals(true, $result);
    }

    /**
     * @covers \DB
     */
    public function testInsertUpdate()
    {
        $pdo = $this->init();
        $total = $pdo->getRowCount('guestbook');
        $id = $total+1;

        $row = $pdo->insertUpdate('guestbook', array(
            'id' => $id,
            'content' => 'insertUpdate',
            'user' => "iuboy".$id,
            'created' => '2021-01-24 00:00:00'
        ));

        $row = $pdo->row_array("SELECT id FROM guestbook WHERE user = :name", array('name'=>'iuboy'.$id));
        // $row = $pdo->select("SELECT * FROM guestbook");

        $this->assertEquals($id, $row['id']);
    }

    /**
     * @covers \DB
     */
    public function testShowQuery()
    {
        $pdo = $this->init();
        $row = $pdo->row_array("SELECT id FROM guestbook WHERE user = :name", array('name'=>'joe'));
        $this->assertEquals('SELECT id FROM guestbook WHERE user = :name', $pdo->showQuery());
    }

    /**
     * @covers \DB
     */
    public function testBeginTransaction()
    {
        $pdo = $this->init();
        $total = $pdo->getRowCount('guestbook');
        $id = $total+1;
        $pdo->beginTransaction();
        $row = $pdo->insert('guestbook', array(
            'id' => $id,
            'content' => 'BeginCommit',
            'user' => "boy{$id}",
            'created' => '2021-01-24 00:00:00'
        ));
        $result = $pdo->commit();
        $this->assertEquals(true, $result);
    }

    /**
     * @covers \DB
     */
    public function testRollback()
    {
        $pdo = $this->init();
        $total = $pdo->getRowCount('guestbook');
        $id = $total+1;
        $pdo->beginTransaction();
        $row = $pdo->insert('guestbook', array(
            'id' => $id,
            'content' => 'BeginRollBack',
            'user' => "boy{$id}",
            'created' => '2021-01-24 00:00:00'
        ));
        $result = $pdo->rollback();
        $this->assertEquals(true, $result);
    }

    /**
     * @covers \DB
     */
    public function testShowColumns()
    {
        $pdo = $this->init();
        $result = $pdo->showColumns('guestbook');
        $this->assertEquals('text', $result['column']['user']);

        $result2 = $pdo->showColumns('users');
        $this->assertEquals('id', $result2['primary']);
    }

    /**
     * @covers \DB
     */
    public function testSetFetchMode()
    {
        $pdo = $this->init();
        $pdo->setFetchMode(PDO::FETCH_CLASS);
        $row = $pdo->row_array("SELECT id FROM guestbook WHERE user = :name", array('name'=>'joe'));
        $this->assertEquals(1, $row->id);
    }

    /**
     * @covers \DB
     */
    public function testSelectException() : void
    {
        $pdo = $this->init();

        // Assert
        $this->expectException(Exception::class);

        // Act
        $res = $pdo->select("SELECT id FROM guestbook WHERE user = :name", 'wrongParam');
    }

    /**
     * @covers \DB
     */
    public function testGetRowCountException() : void
    {
        $pdo = $this->init();
        $pdo->debug = 0;
        $row = $pdo->getRowCount('wrongTable');
        $this->assertEquals(FALSE, $row);
    }

    /**
     * @covers \DB
     */
    public function testsetCharsetException() : void
    {
        $pdo = $this->init();
        $pdo->debug = 0;
        $res = $pdo->setCharset("wrongCharset");
        $this->assertEquals(FALSE, $res);
    }

    /**
     * @covers \DB
     */
    // public function testSetCharsetError()
    // {
    //     $pdo = $this->init();
    //     $pdo->setCharset('MIT');
    // }

    /**
     * @covers \DB
     */
    // public function test_prepareAndBind()
    // {
    //     $pdo = $this->init();
    //     $reflector = new ReflectionMethod(DB::class, '_prepareAndBind');
    //     $reflector->setAccessible(true);

    //     $this->assertEquals('', $reflector->invoke($pdo));
    // }
}