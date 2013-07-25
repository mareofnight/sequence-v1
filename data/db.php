<?php

require_once("config.inc.php");

class DB {

// connection parameters
private $server = '';
private $username = '';
private $password = '';
private $database = '';
private $type = '';

// other parameters
private $pdo = '';
private $error = '';

/**
 *  Constructor
 *  @param $type the type of database being connected to
 *  @param $server the URI of the database server
 *  @param $user the username of the database user to connect as
 *  @param $pass the password of the database user to connect as
 *  @param $data the name of the database to connect to
 **/
function __construct($type = 'mysql', $server = DB_SERVER, $user = DB_USER, $pass = DB_PASS, $data = DB_DATABASE) {
    // set connection parameters
    $this->type = $type;
    $this->server = $server;
    $this->username = $user;
    $this->password = $pass;
    $this->database = $data;
    
    // connect
    $this->connect();
}

/**
 * connect to the database
 * (constructor connects by default, but you can reconnect with this if you get disconnected)
 **/
function connect() {
    try {
        if ($this->type == 'mysql') {
            $this->pdo = new PDO(('mysql:host='.$this->server.';dbname='.$this->database), $this->username, $this->password);
        }
        else {
            $this->error = "db.php does not support the database type '".$this->type."'.";
        }
    }
    catch (PDOException $x) {
        $this->error = $x->getMessage();
    }
}

/**
 * get the most recent error message
 * @return the most recent error message from the database or from this class
 **/
function getError() { return $this->error; }

/**
 * close the connection to the database
 **/
function close() { $this->pdo = null; }

/**
 * Run a prepared statement
 * If running multiple rounds at once, the results of each round will be written over by the previous, so information-getting functions will only get things from the final round
 * @param $query the query string
 * @param $params two dimensional array of the parameters for each round
 * @return an array of the records returned from each round in the format [round[record[column]]] (for SELECTs), or an array of the records affected by each round (for others)
 **/
function run($query, $params) {
    // check if connected
    
    // variables
    $querytype = strtoupper(substr(trim($query), 0, strpos($query, ' ')));
    
    // setup storage
    $results = array();// add the results of each iteration of the query
    
    // make prepared statement
    $stmt = $this->pdo->prepare($query);
    
    // reassign parameter keys if they start at zero
    $paramkeys = array_keys($params[0]);
    if ($paramkeys[0] == 0) {
        $paramsfixkeys = $params;
        $params = array();
        foreach ($paramsfixkeys as $roundfixkeys) {
            $round = array();
            foreach ($roundfixkeys as $oldkey=>$value) {
                $round[$oldkey+1] = $value;
            }
            $params[] = $round;
        }
    }
    
    // bind parameters
    $roundparams = $params[0];
    foreach ($roundparams as $param=>$value) {
        try {
            $stmt->bindParam($param, $roundparams[$param]);
        }
        catch (PDOException $x) {
            $this->error = $x->getMessage();
        }
    }
    
    // run each round of the query
    foreach ($params as $round) {
        // update bound variables
        //$roundparams = $param;
        foreach ($roundparams as $key=>$value) {
            $roundparams[$key] = $round[$key];
        }
        
        // execute
        try {
            $stmt->execute();
            
            // get results
            if (strcmp($querytype, 'SELECT') == 0) {
                $results[] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            else {
                $results[] = $stmt->rowCount();
            }
        }
        catch (PDOException $x) {
            $this->error = $x->getMessage();
        }
        
        // update errors
        $errorInfo = $stmt->errorInfo;
        if (count($errorInfo)>0) {
            $this->error = 'SQLSTATE: '.$errorInfo[0].' DRIVER_ERROR_CODE: '.$errorInfo[1].' ERROR: '.$errorInfo[2];
        }
    }
    
    return $results;
}

}// end class

?>