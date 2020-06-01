<?php

class user {
    
    public $lastError;
    public $result;
    private $DB;
    
    public function __construct()
    {
        $this->DB = Database::getInstance();
    }
    
    public function add($params)
    {
        $userID = intval($params[2] ?? 0);
        $addUserID = intval($_POST['childUserID'] ?? 0);
        
        if($addUserID == 0){
            $this->lastError = array('description' => "Wrong new user id");
            return FALSE; 
        }
        
        $SQL = "SELECT clanID FROM users WHERE id = ?;";
        $request = $this->DB->loadData($SQL, array($userID));
        
        if($request['count'] == 0){
            $this->lastError = array('description' => "user not found");
            return FALSE;
        }
        
        $row = $request['data'][0];
        
        if( !isset($row['clanID'])){
            $this->lastError = array('description' => "Dont have clan");
            return FALSE;
        } 
        
        $SQL = "UPDATE users SET clanID = ?, roleID = 3 WHERE id = ? and clanID IS NULL";
        $request = $this->DB->loadData($SQL, array($row['clanID'], $addUserID));

        if($request['affected_rows'] == 0){
            $this->lastError = array('description' => "User set in other clan or not exist.");
            return FALSE;
        }
        
        $this->result = array('description' => "Added successfully");
        return TRUE;
    }
    
    public function remove($params)
    {
        $userID = intval($params[2] ?? 0);
        $removeUserID = intval($_POST['childUserID'] ?? 0);
        
        $SQL = "SELECT roleID, clanID FROM users WHERE id = ?;";
        $request = $this->DB->loadData($SQL, array($userID));
        
        if($request['count'] == 0){
            $this->lastError = array('description' => "user not found");
            return FALSE;
        }
        
        $row = $request['data'][0];
        
        //При более однородном распределнии прав можно использовать битовую маску
        if( !isset($row['roleID']) || ($row['roleID'] != 1) ){
            $this->lastError = array('description' => "Not enough rights");
            return FALSE;
        } 
        
        $SQL = "UPDATE users SET clanID = NULL, roleID = NULL WHERE id = ? AND clanID = ? and roleID = 3;";
        $request = $this->DB->loadData($SQL, array($removeUserID, $row['clanID']));
        
        if($request['affected_rows'] == 0){
            $this->lastError = array('description' => "User cant removed");
            return FALSE;
        }
        
        $this->result = array('description' => "Removed successfully");
        return TRUE;
    }
    
    public function change($params)
    {
        $userID = intval($params[2] ?? 0);
        $childUserID = intval($_POST['childUserID'] ?? 0);
        $role = intval($_POST['role'] ?? 0);
        
        if( ($role < 0) || ($role > 3)) {
            $this->lastError = array('description' => "Wrong input data");
            return FALSE;
        }
        
        $SQL = "SELECT roleID, clanID FROM users WHERE id = ?;";
        $request = $this->DB->loadData($SQL, array($userID));
        
        if($request['count'] == 0){
            $this->lastError = array('description' => "user not found");
            return FALSE;
        }
        
        $row = $request['data'][0];
        
        if(!isset($row['clanID'])){
            $this->lastError = array('description' => "Not a clan member");
            return FALSE;
        } 
        
        $SQL = "SELECT roleID, clanID FROM users WHERE id = ? and clanID = ?;";
        $requestChild = $this->DB->loadData($SQL, array($childUserID, $row['clanID']));
        
        if($requestChild['count'] == 0){
            $this->lastError = array('description' => "Child user not found or nozt exist");
            return FALSE;
        }
        
        $rowChild = $requestChild['data'][0];
        
        if( ($row['roleID'] == 2) && ($rowChild['roleID'] == 3) && ($role == 2)){
            
            $SQL = "UPDATE users SET roleID = 2 WHERE id = ?;";
            $this->DB->loadData($SQL, array($childUserID));        
            $this->result = array('description' => "Rights changed successfully");
            return TRUE;
        }
        
        if($row['roleID'] == 1){
            
            $SQL = "UPDATE users SET roleID = ? WHERE id = ?;";
            $this->DB->loadData($SQL, array($role, $childUserID));        
            $this->result = array('description' => "Rights changed successfully");
            return TRUE;
        }
        
        $this->lastError = array('description' => "Error with changing rights. Not enough rights.");
        return FALSE;
    }
    
    
    public function __call($method, $parameters) 
    {
        $this->lastError = array('description' => 'function not exist');  
        return FALSE;
    }
    
}