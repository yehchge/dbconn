<?php declare(strict_types=1);

require __DIR__.'/../src/MySqlDB.php';

use PHPUnit\Framework\TestCase;

class MySqlDBTest extends TestCase
{

    protected function setUp(): void
    {
        // MySQLi
        $mysqli = $this->init();

        $mysqli->query('CREATE TABLE IF NOT EXISTS guestbook (id int not null AUTO_INCREMENT, content text, user text, created text, PRIMARY KEY(id))');
        $mysqli->query('CREATE TABLE IF NOT EXISTS users (id int not null AUTO_INCREMENT, username text, PRIMARY KEY (id))');
        $mysqli->query('use '.$_ENV['database']);
        $mysqli->query('TRUNCATE guestbook');
        $mysqli->query('INSERT INTO guestbook(id,content,user,created) VALUE (1,"Hello buddy!","joe","2010-04-24 17:15:23")');
        $mysqli->query('INSERT INTO guestbook(id,content,user,created) VALUE (2,"I like it!","nancy","2010-04-26 12:14:20")');
    }

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
        $mysqli = new MySqlDB($_ENV['hostname'], $user, $password, $database);
        return $mysqli;
    }

    /**
     * @covers \MySqlDB
     */
    public function testCount()
    {
        $mysqli = $this->init();
        $count = $mysqli->count('guestbook');
        $this->assertEquals(2, $count);
    }

    /**
     * @covers \MySqlDB
     */
    public function testCount2()
    {
        $mysqli = $this->init();
        
        // Assert
        $this->expectException(Exception::class);

        // Act
        $count = $mysqli->count('wrongTable');
    }    

    /**
     * @covers \MySqlDB
     */
    public function testSelect()
    {
        $mysqli = $this->init();
        $row = $mysqli->first("SELECT id FROM guestbook WHERE user = 'joe'");
        $this->assertEquals(1, $row['id']);
    }

    /**
     * @covers \MySqlDB
     */
    public function testvInsert()
    {
        $mysqli = $this->init();
        $total = $mysqli->count('guestbook');
        $id = $total+1;
        $row = $mysqli->vInsert('guestbook', array(
            'id' => $id,
            'content' => 'Good Insert',
            'user' => "boy{$id}",
            'created' => '2021-01-24 00:00:00'
        ));
        $row = $mysqli->first("SELECT id FROM guestbook WHERE user = 'boy{$id}'");
        $this->assertEquals($id, $row['id']);
    }

    /**
     * @covers \MySqlDB
     */
    public function testUpdate()
    {
        $mysqli = $this->init();
        $total = $mysqli->count('guestbook');
        $id = $total+1;
        $data = array(
            'content' => 'iLoveFish'           
        );
        $where = "id = 2";
        $result = $mysqli->vUpdate('guestbook', $data, $where);

        $this->assertEquals(true, $result);
    }

    /**
     * @covers \MySqlDB
     */
    public function testDelete(){
        $mysqli = $this->init();
        $where = 'id = 2';
        $result = $mysqli->delete('guestbook', $where);
        $this->assertEquals(true, $result);
    }

    /**
     * @covers \MySqlDB
     */
    public function testDelete2(){
        $mysqli = $this->init();

        // Assert
        $this->expectException(Exception::class);

        // Act
        $res = $mysqli->delete('wrongTable', 'id = 2');
    }


    /**
     * @covers \MySqlDB
     */
    public function testAFetchArray(){
        $mysqli = $this->init();
        $iDbq = $mysqli->query("SELECT * FROM guestbook ORDER BY id LIMIT 1");
        $row = $mysqli->aFetchArray($iDbq);
        $this->assertEquals('joe', $row[2]);
    }

    /**
     * @covers \MySqlDB
     */
    public function testAFetchAssoc(){
        $mysqli = $this->init();
        $iDbq = $mysqli->query("SELECT * FROM guestbook ORDER BY id LIMIT 1");
        $row = $mysqli->aFetchAssoc($iDbq);
        $this->assertEquals('joe', $row['user']);
    }

    /**
     * @covers \MySqlDB
     */
    public function testAFetchRow(){
        $mysqli = $this->init();
        $iDbq = $mysqli->query("SELECT * FROM guestbook ORDER BY id LIMIT 1");
        $row = $mysqli->aFetchRow($iDbq);
        $this->assertEquals('joe', $row[2]);
    }


    /**
     * @covers \MySqlDB
     */
    public function testINumFields(){
        $mysqli = $this->init();
        $iDbq = $mysqli->query("SELECT * FROM guestbook ORDER BY id LIMIT 1");
        $iTotal = $mysqli->iNumFields($iDbq);
        $this->assertEquals(4, $iTotal);
    }


    /**
     * @covers \MySqlDB
     */
    public function testINumRows(){
        $mysqli = $this->init();
        $iDbq = $mysqli->query("SELECT * FROM guestbook");
        $iTotal = $mysqli->iNumRows($iDbq);
        $this->assertEquals(2, $iTotal);
    }

    /**
     * @covers \MySqlDB
     */
    public function testIGetInsertId(){
        $mysqli = $this->init();
        $row = $mysqli->vInsert('guestbook', array(
            'content' => 'Insert Third Times',
            'user' => "Girls have a nice day",
            'created' => date('Y-m-d H:i:s')
        ));
        $iTotal = $mysqli->iGetInsertId();
        $this->assertEquals(3, $iTotal);
    }

    /**
     * @covers \MySqlDB
     */
    public function testVCommit(){
        $mysqli = $this->init();
        $mysqli->vBegin();
        try {
            $row = $mysqli->vInsert('guestbook', array(
                'content' => 'test commit',
                'user' => "mary",
                'created' => date('Y-m-d H:i:s')
            ));
            $mysqli->vCommit();
            $row = $mysqli->first("SELECT id FROM guestbook WHERE user = 'mary'");
        } catch (mysqli_sql_exception $exception) {
            $mysqli->rollback();
            // throw $exception;
        }
        $this->assertEquals(3, $row['id']);
    }

    /**
     * @covers \MySqlDB
     */
    public function testVRollback(){
        $mysqli = $this->init();
        $mysqli->vBegin();

        $row = $mysqli->vInsert('guestbook', array(
            'content' => 1234,
            'user' => "john",
            'created' => date('Y-m-d H:i:s')
        ));
        $mysqli->vRollback();
        $row = $mysqli->first("SELECT id FROM guestbook WHERE user = 'john'");

        $this->assertEquals(NULL, $row);
    }

    /**
     * @covers \MySqlDB
     */
    public function testBIsTableExist(){
        $mysqli = $this->init();
        $result = $mysqli->bIsTableExist('guestbook');
        $this->assertEquals(true, $result);
    }

    /**
     * @covers \MySqlDB
     */
    public function testBIsTableExist2(){
        $mysqli = $this->init();
        $result = $mysqli->bIsTableExist('wrongTable');
        $this->assertEquals(false, $result);
    }

    /**
     * @covers \MySqlDB
     */
    public function testBIsDatabaseExist(){
        $mysqli = $this->init();
        $result = $mysqli->bIsDatabaseExist('myguestbook');
        $this->assertEquals(true, $result);
    }

    /**
     * @covers \MySqlDB
     */
    public function testBIsDatabaseExist2(){
        $mysqli = $this->init();
        $result = $mysqli->bIsDatabaseExist('wrongDataBase');
        $this->assertEquals(false, $result);
    }

    /**
     * @covers \MySqlDB
     */
    public function testAGetAllFieldsInfo(){
        $mysqli = $this->init();
        $result = $mysqli->aGetAllFieldsInfo('guestbook');
        $this->assertEquals('content', $result[1]['Field']);
    }

    /**
     * @covers \MySqlDB
     */
    public function testAGetCreateTableInfo(){
        $mysqli = $this->init();
        $result = $mysqli->aGetCreateTableInfo('guestbook');
        $this->assertEquals('guestbook', $result['Table']);
    }


    /**
     * @covers \MySqlDB
     */
    public function testBSetCharacter(){
        $mysqli = $this->init();
        $mysqli->bSetCharacter("utf8mb4");
        $result = $mysqli->character_set_name();
        $this->assertEquals('utf8mb4', $result);
    }

    /**
     * @covers \MySqlDB
     */
    public function testVFreeAll(){
        $mysqli = $this->init();
        $iDbq = $mysqli->query("SELECT * FROM guestbook ORDER BY id LIMIT 2");
        $row = $mysqli->aFetchAssoc($iDbq);
        $mysqli->vFreeAll();
        $row = $mysqli->aFetchAssoc($iDbq);
        $this->assertEquals(NULL, $row);
    }



    

    


    
}