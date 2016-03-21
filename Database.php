<?php
/**
 * @author Nicolas Colbert <ncolbert@zeekee.com>
 * @copyright 2002 Zeekee Interactive
 */
/**
 *                                                                   
 * A database abstraction class
 */
class Database
{
    private $dbh;
    private $database = '';
    private $host = 'localhost';
    private $type = 'mysql';
    private $dbUser = '';
    private $dbPass = '';
    private $dsn = '';
    private $lastCommandError = false;
    private $connectionError = false;
    private $errorMessage = false;
    private $databases = array();
    private $vars;

    /**
     * [__construct]
     * 
     * @param string $database   [the database name]
     * @param string $user       [the user associated with the database connection]
     * @param string $pass       [the password associated with the database connection]
     * @param string $dsn 
     *                           [The Data Source Name, or DSN, contains the information required to connect to
     *                           the database. ]
     */
    public function __construct($database = null, $user = null , $pass = null, $dsn = null, $host = null, $type = null)
    {
        if (!empty($database)) {
            $this->database = $database;
            !empty($user) ? $this->dbUser = $user : false;
            !empty($pass) ? $this->dbPass = $pass : false;
            !empty($dsn)  ? $this->dsn = $dsn : false;
            !empty($host) ? $this->host = $host : false;
            !empty($type) ? $this->type = $type : false;
            $this->connect();
        }
    }

    /**
     * @ignore
     */
    public function __destruct()
    {
        
    }

    /**
     * build a PDO query string
     * 
     * @param  string $table       [the table name to use as a template to build query]
     * @param  string $type        [what type of CRUD operation is occuring]     
     * @param  array  $variables   [list of variables to include in the record update]
     * @param  array  $constraints [list of constraining variables]
     * @param  string $limit       [limit on returned or impacted records]
     * @return string              [A PDO acceptable query string]
     */
    public function buildQuery(
        $table, 
        $type = 'INSERT', 
        $variables = array(), 
        $constraints = array(), 
        $limit = '1')
    {

        $sql = '';
        $fields = $values = $updates = $columns = array();
        $type = strtoupper(trim($type));
        $sth = $this->dbh->prepare('DESCRIBE ' . $table);

        $sth->execute();

        $limit > 0 ? $limit = " LIMIT $limit" : $limit = '';

        $tableinfo = $sth->fetchAll(\PDO::FETCH_ASSOC);
        // add field names by name
        foreach ($tableinfo as $key => $array) {
            strtoupper($array['Key']) == 'PRI' ? $primary = $array['Field'] : false;
            $columns[$array['Field']] = $array;
        }

        if ($type == 'SELECT') {
            foreach ($columns as $key => $array) {
                if (array_key_exists($array['Field'], $variables) && !empty($variables[ $array['Field'] ])) {
                    $updates[] = $array['Field'] . " = :$array[Field]";
                }
            }
            $sql = "SELECT FROM $table WHERE " . implode(' AND ', $updates) . "$limit";
        } elseif ($type == 'DELETE') {
            foreach ($columns as $key => $array) {
                if (array_key_exists($array['Field'], $variables) && !empty( $variables[ $array['Field'] ] )) {
                    $updates[] = $array['Field'] . " = :$array[Field]";
                }
            }
            $sql = "DELETE FROM $table WHERE " . implode(' AND ', $updates) . "$limit";
        } elseif ($type == 'UPDATE') {
            foreach ($columns as $key => $array) {
                if (array_key_exists($array['Field'], $variables) && $primary != $array['Field']) {
                    $updates[] = $array['Field'] . " = :$array[Field]";
                }
            }
            if (sizeof($constraints) > 0) {
                foreach ($constraints as $key => $array) {
                    $where[] = $key . " = :$key";
                }
                $sql = "UPDATE $table SET " . implode(',', $updates) . " WHERE " . implode(' AND ', $where) . "$limit";
            } else {
                $sql = "UPDATE $table SET " . implode(',', $updates) . " WHERE " . $primary . " = :" . $primary . "$limit";
            }
        } else {
            // check to see all null value no fields are accounted for.
            foreach ($columns as $key => $array) {
                if ($array['Field'] != $primary) {
                    if (!array_key_exists($array['Field'], $variables)) {
                        if ($array['Null'] == 'NO') {
                            if (!in_array($array['Field'], $fields)) {
                                $fields[] = $array['Field'];
                                $values[] = "'$array[Default]'";
                            }
                        }
                    } else {
                        $fields[] = $array['Field'];
                        $values[] = ":$array[Field]";
                    }
                }
            }
            $sql = "INSERT INTO $table ( " . implode(' , ', $fields) . ' ) VALUES ( ' . implode(' , ', $values) . " );";
        }
        
        return $sql;
    }

    /**
     * change the database 
     * 
     * @param  string $database [name of the database]
     * 
     * @return none
     */
    public function changeDatabase($database) 
    {
        $this->setDatabase($database);
        $this->connect();
    }

    /**
     * establish a connection to the requested database
     * 
     * @return none
     */
    public function connect()
    {

        $dsn;

        if ($this->type == 'mysql') {
            $dsn = $this->type . ':';            
            strlen($this->database) > 0 ? $dsn .= 'dbname=' . $this->database . ';' : false;             
            $dsn .= 'host=' . $this->host;            
        }

        try {
            $this->dbh = new \PDO($dsn, 
                $this->dbUser, 
                $this->dbPass);  
            $this->dbh->setAttribute( \PDO::MYSQL_ATTR_FOUND_ROWS, true);   
            $this->dbh->setAttribute (\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
            $this->dbh->setAttribute (\PDO::ATTR_ERRMODE, \PDO::ERRMODE_WARNING );
        } catch (\PDOException $e) {
            $this->connectionError = true;
            $this->errorMessage = "PDO Exception!: " . $e->getMessage() . "<br/>";
        } catch (\Exception $e) {
            $this->connectionError = true;
            $this->errorMessage = "Exception!: " . $e->getMessage() . "<br/>";
        }
    }

    /**
     * return the connection
     * 
     */ 
    public function connection()
    {
        return $this->dbh;
    }

    /**
     * return the value stored in the connectionError variable
     * 
     * @return boolean TRUE/FALSE - if the connect command had an issue.
     * 
     */
    public function connectionError() 
    {
        return $this->connectionError;
    }

    /**
     * get current connection settings 
     * 
     * @param string $return [what type or method to be returned]
     * 
     * @return mixed string|array [values for the connection]
     */
    public function connectionInfo()
    {        
        return array(
        'connection' => $this->dbConn,
        'database' => $this->dbName,
        'user' => $this->dbUser,
        'password' => $this->dbPass
        );
    }

    /**
     * 
     * 
     */ 
    public function fillVariables($table, &$variables) 
    {

        $columns = array();
        $columns = $this->describeTable($table);
        foreach ($columns AS $key => $array) {
            if (!array_key_exists( $key, $variables)) {
                $array['Null'] == 'NO' ? $variables[ $key ] = $array['Default'] : false;                        
                (empty( $variables[$array['Field']] ) && substr( $array['Type'], 0, 3 ) == 'int') ? $variables[$array['Field']] = 0 : false;
                (empty( $variables[$array['Field']] ) && $array['Type'] == 'datetime') ? $variables[$array['Field']] = '0000-00-00 00:00:00' : false;            
                (empty( $variables[$array['Field']] ) && $array['Type'] == 'date') ? $variables[$array['Field']] = '0000-00-00' : false;            
            } else {
                (empty( $variables[$array['Field']] ) && substr( $array['Type'], 0, 3 ) == 'int') ? $variables[$array['Field']] = 0 : false;            
                (empty( $variables[$array['Field']] ) && $array['Null'] == 'NO') ? $variables[$array['Field']] = $array['Default'] : false;
            }
        }
    }

    
    /**
     * split the string into the component sql calls.
     * 
     * @param  string $sql [string to be parsed into smaller sql statements.]
     * @return [type]      [description]
     * 
     */
    private function db_split_sql($sql) {
        $sql = trim($sql);
        $sql = preg_replace("/\n#[^\n]*\n/", "\n", $sql);
        $buffer = array();
        $ret = array();
        $in_string = false;

        for($i=0; $i<strlen($sql)-1; $i++) {
            if($sql[$i] == ";" && !$in_string) {
                $ret[] = substr($sql, 0, $i);
                $sql = substr($sql, $i + 1);
                $i = 0;
            }

            if($in_string && ($sql[$i] == $in_string) && $buffer[1] != "\\") {
                $in_string = false;
            } elseif(!$in_string && ($sql[$i] == '"' || $sql[$i] == "'") && (!isset($buffer[0]) || $buffer[0] != "\\")) {
                $in_string = $sql[$i];
            }
            if(isset($buffer[1])) {
                $buffer[0] = $buffer[1];
            }
            $buffer[1] = $sql[$i];
        }

        if(!empty($sql)) {
            $ret[] = $sql;
        }
        return($ret);
    }

    /**
     * 
     * query the currently selected database for properties DESCRIBE
     * 
     * @param  string $table [name of table]
     * 
     * @return array         [array attributes]
     * 
     */
    public function describeTable($table)
    {
        $columns = array();
        $sth = $this->dbh->prepare('DESCRIBE ' . $table);
        $sth->execute();
        $tableinfo = $sth->fetchAll(\PDO::FETCH_ASSOC);
        // add field names by name
        foreach ($tableinfo as $key => $array) {            
            $columns[$array['Field']] = $array;
        }
        return $columns;
    }


    /**
     * 
     * return message from errorInfo command
     * 
     * @return string
     * 
     */
    public function errorInfo()
    {
        return $this->dbh->errorInfo();
    }

    /**
     * 
     * return value of errorMessage value
     * 
     * @return string 
     * 
     */
    public function errorMessage()
    {
        return $this->errorMessage;
    }

    public function fetch($type = 'FETCH_OBJ')
    {
        $tableinfo = $sth->fetchAll(\PDO::FETCH_ASSOC);
    }


    public function getError()
    {
        $err = $this->dbh->errorInfo();
        return $err[2];
    }

    /**
     * pre-defined sql insert command
     * @param  string $table     [the table to update]
     * @param  array  $variables [variables to be used when updating the record]
     * 
     * @return integer    
     */ 
    public function insert($table, $variables)
    {    
        is_object($variables) ? $variables = (array) $variables : false;
        $this->fillVariables($table, $variables);
        $sql = $this->buildQuery($table, 'insert', $variables);
        $fields = $this->setVariables($sql, $variables);
        $sth = $this->runQuery($sql, $fields->vars);
        $err = $sth->errorInfo();
        if ($err[1] > 0) {
            $this->error = $err[2];
            return 0;
        } else {
            return $this->insertId();
        }
    }

    /**
     *  @ignore
     */
    public function isError()
    {
        return $this->error;
    }    

    /**
     * 
     * id from the last insert command with auto-increment.
     * 
     * @return integer
     * 
     */
    public function insertId()
    {
        return $this->dbh->lastInsertId();
    }

    /**
     * [loadRecords]
     * @param  string $sql    [the query to run]
     * @param  array  $params [the parameters to bind to the query string.]
     * @return mixed
     */
    public function loadRecords($sql, $params = array())
    {
        try {
            $sth = $this->dbh->prepare($sql);
            if (!$sth) {
                echo "\nPDO::errorInfo():\n";
                print_r($dbh->errorInfo());
            }
            $sth->execute($params);
            return $sth->fetchAll(\PDO::FETCH_OBJ);           
        } catch ( \PDOException $e) {
            return $e->getCode() . ':' . $e->getMessage();
        } catch ( \Exception $e ) {
            return $e->getCode() . ':' . $e->getMessage();
        }
    }       



    /**
     * this commits the change to the database performs an insert 
     * or update based on id number.
     * 
     */ 
    public function persist($object)
    {
        if (preg_match('@\\\\([\w]+)$@', get_class($object), $matches)) {
            $classname = $matches[1];
        }  
        $table = $classname . 's';      
        
        if ($this->id > 0) {
            // this is an update
            $this->update($table, $object);
        } else {
            $this->insert($table, $object);
        }
    }    

    /**
     * run the provided sql query
     * 
     * @param  string $sql    [the query to run]
     * @param  array  $params [the parameters to bind to the query string.]
     * @return mixed object|array
     * 
     */
    public function runQuery($sql, $params = array())
    {
        $sql = preg_replace("/[\r\n|\n]/", ' ', $sql);
        $sql = preg_replace("/\s+/", ' ', trim($sql));        
        // ensure that only 
        $matches = array();
        foreach ($params as $key => $value) {
            if (preg_match("/\:$key/", $sql)) {
                $matches[$key] = $value;
            }
        }

        try {
            $sth = $this->dbh->prepare($sql);
            if (!$sth) {
                echo "\nPDO::errorInfo():\n";
                print_r($this->errorInfo());
            }
            $sth->execute($matches);     

            $err = $sth->errorInfo();
            
            return $sth;
        } catch ( \PDOException $e) {
            return $e->getCode() . ':' . $e->getMessage();
        } catch ( \Exception $e ) {
            return $e->getCode() . ':' . $e->getMessage();
        }
    } 


    /**
     * [runSql]
     * @param  string $sql    [the query to run]
     * @param  array  $params [the parameters to bind to the query string.]
     * @return mixed
     */
    public function runSql($sql, $params = array())
    {
        try {
            $sth = $this->dbh->prepare($sql);
            if (!$sth) {
                echo "\nPDO::errorInfo():\n";
                print_r($dbh->errorInfo());
            }
            $sth->execute($params);

            if ($sth->rowCount() == 1) {
                return $sth->fetch(\PDO::FETCH_OBJ);
            } else {
                return $sth;
            }
        } catch ( \PDOException $e) {
            return $e->getCode() . ':' . $e->getMessage();
        } catch ( \Exception $e ) {
            return $e->getCode() . ':' . $e->getMessage();
        }
    }       

    /**
     * PDO command to run a SOURCE command File
     * 
     * @param  string $sqlfile [path to .sql file]
     * 
     * @return none
     * 
     */
    public function runSourceCommand($sqlfile) {
        $mqr = @get_magic_quotes_runtime();
        @set_magic_quotes_runtime(0);
        $query = fread(fopen( $sqlfile, 'r' ), filesize( $sqlfile ) );
        @set_magic_quotes_runtime($mqr);
        $pieces  = $this->db_split_sql($query);
        for ($i=0; $i<count($pieces); $i++) {
            $pieces[$i] = trim($pieces[$i]);
            if(!empty($pieces[$i]) && $pieces[$i] != "#") {
                $this->exec($pieces[$i]);
            }
        }
    }

    
    /**
     * set the user/pw combonation to be used by the database connection.
     *                    
     * @param string $user [the user associated with the database connection]
     * @param string $pw   [the password associated with the database connection]
     * 
     * @return Database 
     */
    public function setCredentials($user = '', $pw = '')
    {
        $this->dbUser = $user;
        $this->dbPass = $pw;
        return $this;
    }

    /**
     * set the database variable.
     * 
     * @param string [the name of the database to be used]
     * 
     * @return Database 
     * 
     */
    public function setDatabase($database)
    {
        $this->database = $database;
        return $this;
    }

    /**
     *  set the Host variable.
     * 
     * @param string $dsn [The Data Source Name, or DSN, contains the information required 
     *                    to connect to the database. ]
     * 
     * @return Database 
     */
    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }    

    /**
     * set the variables used in PDO requests to match.
     * 
     * @param string $sql    [sql string to use as map]
     * @param array  $params [array used to populate variables for query]
     * 
     * @return Database
     * @todo  add checks to ensure data follows database structure requirements.
     * 
     */
    public function setVariables($sql, $params = array())
    {
        $vars = array();        
        $sql = str_replace(',', ', ', $sql);
        preg_match_all("/:(.*?)\s/", $sql, $matches);
        foreach ($matches[1] as $value) {
            $value = str_replace(',', '', $value);
            array_key_exists($value, $params) ? $vars[$value] = $params[$value] : false;
        }
        $this->vars = $vars;
        return $this;
    }        

    /**
     * return a list of databases the current user is able to view in the currently seelction connection.
     *                   
     * @return array
     * 
     */
    public function showDatabases()
    {
        $array = array();
        $sth = $this->prepare('SHOW databases');        
        $sth->execute();        
        $tableinfo = $sth->fetchAll(\PDO::FETCH_ASSOC);        
        foreach ($tableinfo as $key => $value) {            
            $array[] = $value['Database'];
        }        
        return $array;
    }

    /**
     * return the list of databases for the selected database.
     *                            
     * @return array
     * 
     */
    public function showTables()
    {
        $array = array();
        $sth = $this->prepare('SHOW tables');        
        $sth->execute();        
        $tableinfo = $sth->fetchAll(\PDO::FETCH_ASSOC);        
        foreach ($tableinfo as $key => $value) {            
            $array[] = $value['Tables_in_' . $this->dbName];
        }        
        return $array;
    }

    /**
     * [sqlQuery]
     * @param  string $sql    [the query to run]
     * @param  array  $params [the parameters to bind to the query string.]
     * @return mixed
     */
    public function sqlQuery($sql, $params = array())
    {
        try {
            $sth = $this->dbh->prepare($sql);
            if (!$sth) {
                echo "\nPDO::errorInfo():\n";
                print_r($dbh->errorInfo());
            }
            $sth->execute($params);

            if ($sth->rowCount() == 1) {
                return $sth->fetch(\PDO::FETCH_OBJ);
            } else {
                return $sth->fetchAll(\PDO::FETCH_OBJ);
            }
        } catch ( \PDOException $e) {
            return $e->getCode() . ':' . $e->getMessage();
        } catch ( \Exception $e ) {
            return $e->getCode() . ':' . $e->getMessage();
        }
    }   

    /**
    * pre-defined to run a sql update command
    * 
    * @param  string $table     [the table to update]
    * @param  array  $variables [variables to be used when updating the record]
    * 
    * @return integer    
    */
    public function update($table, $variables)
    {
     
        is_object($variables) ? $variables = (array) $variables : false;    
        $sql = $this->buildQuery($table, 'update', $variables);
        $fields = $this->setVariables($sql, $variables);
        $sth = $this->runQuery($sql, $fields->vars);
        $err = $sth->errorInfo();

        if ($err[1] > 0) {
            $this->error = $err[2];
            return 0;
        } else {
            if ($err[1] == 0) {
                return 1;
            } else {
                return $sth->rowCount();
            }
        }
    }
   
}
