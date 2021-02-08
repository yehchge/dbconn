<?php 

/**
 * @category Database
 * @example
 * try {
 *    $db = new DB(array('type'=>'mysql', 'host'=>'dbhost','name'=>'dbname','user'=>'dbuser','pass'=>'dbpass'));
 *    $db->select("SELECT * FROM user WHERE id = :id", array('id', 25));
 *    $db->insert("user", array('name' => 'mary'));
 *    $db->update("user", array('name' => 'jackie), "id = '25'");
 *    $db->delete("user", "id = '25'");
 * } catch (Exception $e) {
 *    echo $e->getMessage();
 * }
 */
class DB extends PDO {
    
     // string: last SQL command
    private $_sql;
    
    // constant: select statement fetch mode
    private $_fetchMode = PDO::FETCH_ASSOC;

    public $debug = 1;
    /**
     * Initializes a PDO connection
     * @param array $db An associative array containing the connection settings,
     *
     *    $db = array(
     *        'type' => 'mysql',
     *        'host' => 'localhost',
     *        'name' => 'test',
     *        'user' => 'root',
     *        'pass' => ''
     *    );
     *  $db = new DB($db);
     */
    public function __construct($db, $persistent = false)
    {
        try {
            $dsn = $db['type'].':host='.$db['host'].';dbname='.$db['name'];
            parent::__construct($dsn, $db['user'], $db['pass'], array(
                PDO::ATTR_PERSISTENT => $persistent
            ));

            parent::setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            parent::setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT ); // 預設模式，不主動報錯
            // parent::setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING ); // 引發 E_WARNING 錯誤，主動報錯
            // parent::setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION ); // 主動丟擲 exceptions 異常，需要以try{}cath(){}輸出錯誤資訊。
             
            self::setCharset();
            self::setFetchMode();
        } catch (PDOException $e) {
            die("==>".$e->getMessage().PHP_EOL);
        }
    }
    
    /**
     * @param constant $fetchMode Use the PDO fetch constants, eg: PDO::FETCH_CLASS
     */
    public function setFetchMode($fetchMode = PDO::FETCH_ASSOC)
    {
        $this->_fetchMode = $fetchMode;
    }

    public function setCharset($encode = "utf8") {
        $res = $this->_exec("SET names {$encode}");

        if ($res === FALSE)
        {
            $this->_display_error();
            return FALSE;
        }
        return $res;
    }

    private function _exec($sql){
        // exec use in no return result, such as insert, update, delete etc.
        // exec will return the number of data change.
        $affected = self::exec($sql);
        return $affected;
    }

    private function _execute($sql){
        return $this->query($sql);
    }

    private function _display_error(){
        $error = $this->errorInfo();

        if ($this->errorCode() == '00000') return true;

        $message[] = "A Database Error Occurred";
        $message[] = "Error Number: ".$error[1];
        $message[] = $error[2];

        if ($this->debug==1)
        {
            $trace = debug_backtrace();

            foreach ($trace as $call)
            {
                if (isset($call['file']) && strpos($call['file'], __DIR__) === FALSE)
                {
                    $message[] = 'Filename: '.$call['file'];
                    $message[] = 'Line Number: '.$call['line'];
                    break;
                }
            }

            foreach($message as $msg){
                echo $msg.PHP_EOL;
            }
        }
    }
    

    public function getRowCount($table)
    {
        $this->_sql = "SELECT COUNT(1) FROM $table";
        $res = $this->_execute($this->_sql);
        if($res === FALSE)
        {
            $this->_display_error();
            return FALSE;
        } else {
            $count = $res->fetchColumn();
            return $count;    
        }
    }

    /**
     * @param string $query Build a query with ? marks in the proper order,
     *    eg: SELECT :email, :password FROM tablename WHERE userid = :userid
     * 
     * @param array $bindParams Fields The fields to select to replace the :colin marks,
     *    eg: array('email' => 'email', 'password' => 'password', 'userid' => 200);
     *
     * @return array
     */
    public function select($query, $bindParams = array())
    {
        // try {
            $this->_sql = $query;
            
            if (!is_array($bindParams))
                throw new Exception("$bindParams must be an array");

            $sth = $this->_prepareAndBind($bindParams);

            if($sth === FALSE)
            {
                $this->_display_error();
                return FALSE;
            }

            $res = $sth->execute();

            if($res === FALSE)
            {
                $this->_display_error();
                return FALSE;
            }
            
            // $this->_handleError($res, __FUNCTION__);
            
            return $sth->fetchAll($this->_fetchMode);
        // } catch (PDOException $e) {
        //     die($e->getMessage().PHP_EOL);
        // }    
    }

    /**
     * Returns a single result row 
     * @access  public
     * @return  array
     */
    public function row_array($query, $bindParams = array())
    {
        try {
            $this->_sql = $query;
            
            if (!is_array($bindParams))
                throw new Exception("$bindParams must be an array");
            
            $sth = $this->_prepareAndBind($bindParams);

            if($sth === FALSE)
            {
                $this->_display_error();
                return FALSE;
            }
        
            $res = $sth->execute();

            if($res === FALSE)
            {
                $this->_display_error();
                return FALSE;
            }
            
            $this->_handleError($res, __FUNCTION__);

            $result = $sth->fetchAll($this->_fetchMode);

            if (!isset($result[0]))
            {
                return array();
            }

            return $result[0];
        } catch (PDOException $e) {
            die($e->getMessage().PHP_EOL);
        }            
    }

    /**
     * @param string $table  The table to insert into
     * @param array $data    An associative array of data: field => value
     */
    public function insert($table, $data)
    {  
        try {
            $insertString = $this->_prepareInsertString($data);

            $this->_sql = "INSERT INTO `{$table}` (`{$insertString['names']}`) VALUES({$insertString['values']})";

            $sth = $this->_prepareAndBind($data);

            if($sth === FALSE)
            {
                $this->_display_error();
                return FALSE;
            }

            $res = $sth->execute();

            if($res === FALSE)
            {
                $this->_display_error();
                return FALSE;
            }
            
            $this->_handleError($res, __FUNCTION__);
            
            return $this->lastInsertId();
        } catch (PDOException $e) {
            die($e->getMessage().PHP_EOL);
        }
    }

    /**
     * @param string $table  The table to insert into
     * @param array $data    An associative array of data: field => value
     */
    public function exec_insert($table, $data)
    {   
        try {
            $insertString = array(
                'names' => implode("`, `",array_keys($data)),
                'values' => implode("', '",array_values($data))
            );

            $this->_sql = "INSERT INTO `{$table}` (`{$insertString['names']}`) VALUES('{$insertString['values']}')";

            $result = $this->exec($this->_sql);
            
            $this->_handleError($result, __FUNCTION__);
            
            return $result;
        } catch (PDOException $e) {
            die($e->getMessage().PHP_EOL);
        }

    }

    /**
     * @param string $table
     * @param array $data eg: field => value
     * @param string $where
     * @param array $bindWhereParams
     * @return boolean Successful or not
     */
    public function update($table, $data, $where, $bindWhereParams = array())
    {
        try {
            $updateString = $this->_prepareUpdateString($data);

            $this->_sql = "UPDATE `{$table}` SET $updateString WHERE $where";
            
            $sth = $this->_prepareAndBind($data);

            if($sth === FALSE)
            {
                $this->_display_error();
                return FALSE;
            }

            $sth = $this->_prepareAndBind($bindWhereParams, $sth);

            if($sth === FALSE)
            {
                $this->_display_error();
                return FALSE;
            }
            
            $result = $sth->execute();
            
            $this->_handleError($result, __FUNCTION__);
            
            return $result;
        } catch (PDOException $e) {
            die($e->getMessage().PHP_EOL);
        }
    }
    
    /**
    * @param string $table 
    * @param string $where 
    * @param array $bindWhereParams 
    * @return integer Total affected rows
    */
    public function delete($table, $where, $bindWhereParams = array())
    {
        try {
            $this->_sql = "DELETE FROM `{$table}` WHERE $where";
            
            $sth = $this->_prepareAndBind($bindWhereParams);

            if($sth === FALSE)
            {
                $this->_display_error();
                return FALSE;
            }      
            
            $result = $sth->execute();

            $this->_handleError($result, __FUNCTION__);
            
            return $sth->rowCount();
        } catch (PDOException $e) {
            die($e->getMessage().PHP_EOL);
        }
    }
    
    /**
     * @param string $table 
     * @param array $data eg: field => value
     */
    public function insertUpdate($table, $data)
    {
        try {
            $insertString = $this->_prepareInsertString($data);
            $updateString = $this->_prepareUpdateString($data);

            $this->_sql = "INSERT INTO `{$table}` (`{$insertString['names']}`) VALUES({$insertString['values']}) ON DUPLICATE KEY UPDATE {$updateString}";

            $sth = $this->_prepareAndBind($data);

            if($sth === FALSE)
            {
                $this->_display_error();
                return FALSE;
            }

            $result = $sth->execute();

            // $this->_handleError($result, __FUNCTION__);
       
            return $this->lastInsertId();
        } catch (PDOException $e) {
            die($e->getMessage().PHP_EOL);
        }
    }
    
    /**
     * Return the last sql Query called
     * @return string
     */
    public function showQuery() {
        return $this->_sql;
    }
        
    /**
     * last inserted ID
     * @return integer
     */
    public function id() {
        return $this->lastInsertId();
    }
    
    public function beginTransaction() {
        parent::beginTransaction();
    }

    public function commit() {
        return parent::commit();
    }

    public function rollback() {
        return parent::rollback();
    }
    
    /**
     * showColumns - Display the columns for a table (MySQL)
     * @param string $table Name of a MySQL table
     */
    public function showColumns($table)
    {
        $result = $this->select("SHOW COLUMNS FROM `$table`", array(), PDO::FETCH_ASSOC);
        
        $output = array();
        foreach ($result as $key => $value)
        {
            if ($value['Key'] == 'PRI')
            $output['primary'] = $value['Field'];
            
            $output['column'][$value['Field']] = $value['Type'];
        }
        
        return $output;
    }

    /**
     * @param array $data
     * @param object $reuseStatement If you need to reuse the statement to apply another bind
     * @return object
     */
    private function _prepareAndBind($data, $reuseStatement = false)
    {
        if ($reuseStatement == false) {
            $sth = $this->prepare($this->_sql);
            if($sth === false ) return false;
        } else {
            $sth = $reuseStatement;
        }
        
        foreach ($data as $key => $value)
        {
            if (is_int($value)) {
                $sth->bindValue(":$key", $value, PDO::PARAM_INT);
            } else {
                $sth->bindValue(":$key", $value, PDO::PARAM_STR);
            }
        }
        
        return $sth;
    }
    
    /**
     * @param array $data The data to turn into an SQL friendly string
     * @return array
     */
    private function _prepareInsertString($data) 
    {
        /** 
        * @ Incoming $data looks like:
        * $data = array('field' => 'value', 'field2'=> 'value2');
        */
        return array(
            'names' => implode("`, `",array_keys($data)),
            'values' => ':'.implode(', :',array_keys($data))
        );
    }
    
    /**
     * @param array $data
     * @return string
     */
    private function _prepareUpdateString($data) 
    {
        /**
        * $data = array('field' => 'value', 'field2'=> 'value2');
        */
        $fieldDetails = NULL;
        foreach($data as $key => $value)
        {
            $fieldDetails .= "`$key`=:$key, ";
        }
        $fieldDetails = rtrim($fieldDetails, ', ');
        return $fieldDetails;
    }
    
    private function _handleError($result, $method)
    {
        /** If it's an SQL error */
        if ($this->errorCode() != '00000')
        throw new Exception("Error: " . implode(',', $this->errorInfo()));
        
        if ($result === false) 
        {
            $error =  $method . " did not execute properly";
            throw new Exception($error);
        }
    }

}
