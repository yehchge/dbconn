<?php

class MySqlDB extends mysqli
{
    public $sDBName;

    private $result_mode = MYSQLI_STORE_RESULT;

    private $aResult = array();
    private $iQueryCount = 0;

    static protected $aDB = array();

    public function __construct($sHost='',$sUser='',$sPass='',$sDb='')
    {
        if($sDb==='' || $sHost==='' || $sUser==='' || $sPass==='')
            die("MySqlDB::__construct: require connection info");

        $this->sDBName = $sDb;

        parent::init();

        if (!parent::options(MYSQLI_OPT_CONNECT_TIMEOUT, 5)) {
            die('MySqlDB::__construct: Setting MYSQLI_OPT_CONNECT_TIMEOUT failed');
        }

        $iRetry=0;
        while($iRetry<10){
            if(@parent::real_connect($sHost, $sUser, $sPass, $sDb))
                break;

            $iRetry++;
        }

        if(mysqli_connect_errno()!=0)
            die('MySqlDB::__construct: Connection failed for 10 times, error_msg:'.mysqli_connect_error());

        $this->bSetCharacter(); //default utf8
    }

    public function __destruct()
    {
        $this->vFreeAll();
        parent::close();
    }

    public function query(string $sSql, int $result_mode = MYSQLI_STORE_RESULT) {
        $mResult = parent::query($sSql, $result_mode);
        if($mResult==false)
            throw new \Exception("MySqlDB->query: ".$this->error." : SQL= $sSql");

        if(is_object($mResult) && get_class($mResult)=='mysqli_result'){
            $this->iQueryCount++;
            $this->aResult[$this->iQueryCount] = $mResult;
            return $this->iQueryCount;
        }
        return 0;
    }

    public function count(string $table)
    {
        $query = sprintf("SELECT COUNT(1) FROM `%s`", $this->real_escape_string($table));

        $stmt = $this->query($query);

        $row = $this->aFetchRow($stmt);

        return $row[0];    
    }

    /**
     * Returns a single result row 
     * @access  public
     * @return  array
     */
    public function first(string $query)
    {
        try {
            $stmt = $this->query($query);

            $result = $this->aFetchArray($stmt);

            return $result;
        } catch (\Exception $e) {
            die($e->getMessage().PHP_EOL);
        }            
    }

    public function vFreeAll(){
        //free all result
        foreach ($this->aResult as $oResult) {
            $oResult->free();
        }
        unset($this->aResult);
        $this->aResult = array();
    }

    # Fetch function

    public function aFetchAssoc($iDbq){
        if(!isset($this->aResult[$iDbq]))
            return false;

        return $this->aResult[$iDbq]->fetch_assoc();
    }

    public function aFetchArray($iDbq){
        if(!isset($this->aResult[$iDbq]))
            return false;

        return $this->aResult[$iDbq]->fetch_array();
    }

    public function aFetchRow($iDbq) {
        if(!isset($this->aResult[$iDbq]))
            return false;

        return $this->aResult[$iDbq]->fetch_row();
    }

    public function iNumFields($iDbq){
        if(!isset($this->aResult[$iDbq]))
            return false;

        return $this->aResult[$iDbq]->field_count;
    }

    public function iNumRows($iDbq){
        if(!isset($this->aResult[$iDbq]))
            return false;

        return $this->aResult[$iDbq]->num_rows;
    }

    # Insert Update Delete function

    public function vInsert($sTable,$aInsert){
        if(!is_array($aInsert) || empty($aInsert))
            return;

        //make prepare sql, and count type
        $aField = array_keys($aInsert);
        $sPrepareSql = "INSERT INTO $sTable (";
        $sPostSql = " VALUES (";
        $sPrepareType = '';
        foreach ($aField as $iIndex => $sFieldName) {
            if($iIndex != 0){
                $sPrepareSql .= ",";
                $sPostSql .= ",";
            }

            $sPrepareSql .= "`$sFieldName`";
            $sPostSql .= "?";
            $sPrepareType .= 's';
        }
        $sPrepareSql .= ")".$sPostSql.")";

        //prepare dynamic number of insert value, first value in args is type describe in string, others are insert values
        $args = array();
        $args[] = $sPrepareType;
        foreach($aInsert as $key => $value){
            $args[] = &$aInsert[$key];
        }

        try{
            if(!$stmt = $this->prepare($sPrepareSql))
                throw new Exception("prepare failed: $sPrepareSql");

            //handling dynamic insert number
            $method = new ReflectionMethod('mysqli_stmt', 'bind_param');
            $method->invokeArgs($stmt, $args);

            $stmt->execute();
        }catch(\Exception $e){
            throw new \Exception("CMySQLi->vInsert: ".$e->getMessage());
        }
    }

    public function vUpdate($sTable,$aUpdate,$sWhere){
        if(!is_array($aUpdate) || empty($aUpdate))
            return;

        //make prepare sql, and count type
        $aField = array_keys($aUpdate);
        $sPrepareSql = "UPDATE $sTable SET ";
        $sPrepareType = '';
        foreach ($aField as $iIndex => $sFieldName) {
            if($iIndex != 0){
                $sPrepareSql .= ",";
            }

            $sPrepareSql .= "`$sFieldName`=?";
            $sPrepareType .= 's';
        }
        $sPrepareSql .= " WHERE $sWhere";

        //prepare dynamic number of insert value, first value in args is type describe in string, others are insert values
        $args = array();
        $args[] = $sPrepareType;
        foreach($aUpdate as $key => $value){
            $args[] = &$aUpdate[$key];
        }

        try{
            if(!$stmt = $this->prepare($sPrepareSql))
                throw new \Exception("prepare failed: $sPrepareSql");

            //handling dynamic insert number
            $method = new ReflectionMethod('mysqli_stmt', 'bind_param');
            $method->invokeArgs($stmt, $args);

            return $stmt->execute();
        }catch(\Exception $e){
            throw new \Exception("MySqlDB->vUpdate: ".$e->getMessage());
        }
    }

    public function delete($sTable, $sWhere){
        try{
            if(!$this->bIsTableExist($sTable))
                throw new \Exception("table not exist");

            $this->query("DELETE FROM `$sTable` WHERE $sWhere");
            return $this->affected_rows;
        }catch(\Exception $e){
            throw new \Exception("MySqlDB->delete: ".$e->getMessage());
        }
    }

    public function iGetInsertId(){
        return $this->insert_id;
    }

    # Transaction function

    public function vBegin() {
        $this->query("begin");
    }

    public function vCommit() {
        $this->query("commit");
    }

    public function vRollback() {
        $this->query("rollback");
    }

    # Check Exist function

    public function bIsTableExist(string $sTable){
        $table = $this->real_escape_string($sTable);
        $stmt = $this->query("SHOW TABLES LIKE '%$table%'");
        
        if($this->iNumRows($stmt))
            return true;
        return false;
    }

    public function bIsDatabaseExist(string $sDatabase){
        $dbname = $this->real_escape_string($sDatabase);
        $iDbq = $this->query("SHOW DATABASES LIKE '$dbname'");

        if($this->iNumRows($iDbq))
            return true;
        return false;
    }

    # Table Info function

    public function aGetAllFieldsInfo(string $sTable){
        $table = $this->real_escape_string($sTable);
        $iDbq = $this->query("SHOW FULL FIELDS FROM $table");
        while($aRow = $this->aFetchArray($iDbq))
            $aFields[]=$aRow;

        return $aFields;
    }

    public function aGetCreateTableInfo(string $sTable){
        $table = $this->real_escape_string($sTable);
        $this->query("SET SQL_QUOTE_SHOW_CREATE = 1");

        $iDbq = $this->query("SHOW CREATE TABLE $table");
        
        return $this->aFetchArray($iDbq);
    }

    # Other function 

    public function bSetCharacter($encode = "utf8") {
        $encode = $this->real_escape_string($encode);
        $this->query("SET character_set_client = $encode");
        $this->query("SET character_set_results = $encode");
        $this->query("SET character_set_connection = $encode");
    }

    // DEPRECATED FUNCTIONS
    /* OLD SYLE INSERT WITHOUT PREPARE */
}
