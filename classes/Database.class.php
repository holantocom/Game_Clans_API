<?php

class Database {
    
    private $_connection;
    private static $_instance;
	
    private $_host = "";
    private $_username = "";
    private $_password = "";
    private $_database = "";

    private function __construct() 
    {
        
        $this->_connection = new mysqli($this->_host, $this->_username, $this->_password, $this->_database);
        $this->_connection->query("SET NAMES 'utf8'"); 
        $this->_connection->query("SET CHARACTER SET 'utf8'");
        $this->_connection->query("SET SESSION collation_connection = 'utf8_general_ci'");
	
        if(mysqli_connect_error()) {
            trigger_error(json_encode(['errors' => true, 'description' => 'MySQL connect error']), E_USER_ERROR);
        }
		
    }
	
    public static function getInstance() 
    {
        if(!self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function __clone() { }
	
    public function getConnection() 
    {
        return $this->_connection;
    }
    
    public function loadData($SQL, $params = array(), $returns = false)
    {

        $query = $this->_connection->prepare($SQL);

        if($query === FALSE){
            trigger_error(json_encode(['errors' => true, 'description' => 'Prepare SQL error']), E_USER_ERROR);
        }

        if(count($params) > 0) {
            $params = array_values($params);
            $types = $this->prepareParams($params);
            $query->bind_param($types, ...$params);
        }

        $query->execute();

        if($query === FALSE){
            trigger_error(json_encode(['errors' => true, 'description' => 'Execute SQL error']), E_USER_ERROR);
        }

        $result = $query->get_result();
        $answer['insert_id'] = $query->insert_id;
        $answer['affected_rows'] = $query->affected_rows;
        $count = ($result !== FALSE) ? $result->num_rows : 0;
        $answer['count'] = $count;
        $answer['data'] = [];

        if($count > 0) {
            while ($row = $result->fetch_assoc()) {
                $answer['data'][] = $row;
            }
        }

        $query->close();

        return $answer;
    }

    private function prepareParams($params)
    {
        $typesString = '';
        foreach ($params as $value) {
            if (is_int($value) ) {
                $typesString.='i';
            }
            if (is_double($value) || is_float($value)) {
                $typesString.='d';
            }
            if (is_string($value) || is_null($value)) {
                $typesString.='s';
            }
        }
        return $typesString;
    }
	
}