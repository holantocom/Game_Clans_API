<?php

class clan {
    
    public $lastError;
    public $result;
    private $DB;
    
    public function __construct()
    {
        $this->DB = Database::getInstance();
    }
    
    public function create($params)
    {
        $userID = intval($params[2] ?? 0);
        $name = $_POST['name'] ?? '';
        $desc = $_POST['desc'] ?? '';
        $users = json_decode($_POST['users'] ?? '[]', true);
        
        if(
        	!preg_match('/^[A-Za-z0-9А-Яа-яёË]{1,12}$/u', $name) ||
            (mb_strlen($desc) > 30)
        ){
            $this->lastError = array('description' => "Wrong input params");
            return FALSE; 
        }

        $SQL = "SELECT id FROM users WHERE id = ? AND clanID IS NULL;";
        $request = $this->DB->loadData($SQL, array($userID));
        
        if($request['count'] == 0){
            $this->lastError = array('description' => "user not found");
            return FALSE;
        }
        
        $SQL = "INSERT INTO clans (name, description) VALUES (?, ?)";
        $request = $this->DB->loadData($SQL, array($name, $desc));
        $clanID = $request['insert_id'];
        
        foreach($users as $user){
            $SQL = "UPDATE users SET clanID = ?, roleID = 3 WHERE id = ? AND clanID IS NULL";
            $this->DB->loadData($SQL, array($clanID, $user));
        }
        
        $SQL = "UPDATE users SET clanID = ?, roleID = 1 WHERE id = ?";
        $this->DB->loadData($SQL, array($clanID, $userID));
        
        $this->result = array('description' => "Created successfully");
        return TRUE;
    }
    
    public function remove($params)
    {
        $userID = intval($params[2] ?? 0);
        
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
        
        $SQL = "UPDATE clans 
                INNER JOIN users ON users.clanID = clans.ID
                SET clans.isDeleted = 1, users.clanID = NULL, users.roleID = NULL 
                WHERE clans.id = ?";
        $this->DB->loadData($SQL, array($row['clanID']));
        
        $this->result = array('description' => "Removed successfully");
        return TRUE;
    }
    
    public function lists($params)
    {
        $SQL = "SELECT 
                    clans.id as 'clanID'
                    , clans.name as 'clanName'
                    , clans.description
                    , users.id as 'userID'
                    , users.name as 'userName'
                    , role.name as 'userRole'
                FROM clans
                INNER JOIN users ON clans.ID = users.clanID
                INNER JOIN role ON users.roleID = role.ID
                WHERE clans.isDeleted IS NULL OR clans.isDeleted = 0";
        $request = $this->DB->loadData($SQL);
        
        $answer = array();
        
        foreach($request['data'] as $row){
            
            if(!isset($answer[$row['clanID']])){
                $answer[$row['clanID']] = [
                        'id' => $row['clanID'],
                        'name' => $row['clanName'],
                        'desc' => $row['description']
                    ];
            }    
            
            $answer[$row['clanID']]['users'][] = [
                    'id' => $row['userID'],
                    'name' => $row['userName'],
                    'role' => $row['userRole']
                ];
        }
        
        $this->result = $answer;
        return TRUE;
    }
    
    public function desc($params)
    {
        $userID = intval($params[2] ?? 0);
        $desc = $_POST['desc'] ?? '';

        if(mb_strlen($desc) > 30){
            $this->lastError = array('description' => "Wrong input params");
            return FALSE; 
        }
        
        $SQL = "SELECT roleID, clanID FROM users WHERE id = ?;";
        $request = $this->DB->loadData($SQL, array($userID));
        
        if($request['count'] == 0){
            $this->lastError = array('description' => "user not found");
            return FALSE;
        }
        
        $row = $request['data'][0];
        
        //При более однородном распределнии прав можно использовать битовую маску
        if( !isset($row['roleID']) || ($row['roleID'] >= 3) ){
            $this->lastError = array('description' => "Not enough rights");
            return FALSE;
        }
        
        $SQL = "UPDATE clans SET description = ? WHERE id = ?;";
        $this->DB->loadData($SQL, array($desc, $row['clanID']));
        
        $this->result = array('description' => "Updated successfully");
        return TRUE;
        
    }
    
    public function __call($method, $parameters) 
    {
        $this->lastError = array('description' => 'function not exist');  
        return FALSE;
    }
    
}